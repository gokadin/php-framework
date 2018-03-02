<?php

namespace Library\Core;

use Library\Authentication\Authenticator;
use Library\Container\Container;
use Library\DataMapper\DataMapper;
use Library\Engine\Engine;
use Library\Routing\Router;

class AppConfigurator
{
    const CONFIG_DIRECTORY_NAME = 'Config';
    const FEATURES_CONFIG_DIRECTORY_NAME = 'FeaturesConfig';
    const FEATURES_FILE_NAME = 'features.php';
    const SCHEMA_DIRECTORY_NAME = 'Schema';
    const SCHEMA_FILE_NAME = 'schema.php';
    const STORAGE_DIRECTORY_NAME = 'Storage';
    const PREVIOUS_SCHEMA_FILE_NAME = 'previousSchema.json';

    /**
     * @var Container
     */
    private $container;

    /**
     * @var string
     */
    private $basePath;

    /**
     * @var string
     */
    private $configPath;

    /**
     * @var string
     */
    private $featuresConfigPath;

    /**
     * @var array
     */
    private $features;

    /**
     * ContainerConfiguration constructor.
     *
     * @param Container $container
     * @param string $basePath
     */
    public function __construct(Container $container, string $basePath)
    {
        $this->container = $container;
        $this->basePath = $basePath;
        $this->configPath = $basePath.'/'.self::CONFIG_DIRECTORY_NAME.'/';
        $this->featuresConfigPath = $this->configPath.self::FEATURES_CONFIG_DIRECTORY_NAME.'/';
    }

    /**
     * Configures the container with all of the framework
     * classes plus the ones defined by the user.
     */
    public function configure()
    {
        $this->readFeatures();
        $this->setUpContainer();
    }

    /**
     * Read all configuration files necessary to set up the container.
     *
     * @throws CoreException
     */
    private function readFeatures(): void
    {
        $file = $this->configPath.self::FEATURES_FILE_NAME;
        if (!file_exists($file))
        {
            throw new CoreException('Could not find features file.');
        }

        $this->features = require $file;
    }

    /**
     * @param string $featureName
     * @return array
     * @throws CoreException
     */
    private function readFeatureConfig(string $featureName): array
    {
        $configFile = $this->featuresConfigPath.$featureName.'.php';
        if (!file_exists($configFile))
        {
            throw new CoreException('Could not find config file for '.$featureName.'.');
        }

        return require $configFile;
    }

    /**
     * Register all necessary classes in the container
     * to be resolved later.
     */
    private function setUpContainer(): void
    {
        $this->registerFeatures();
        $this->registerEssentials();
    }

    /**
     * Registers essential classes needed by the framework.
     */
    private function registerEssentials(): void
    {
        $this->registerRouter();
    }

    /**
     * Registers optional features.
     */
    private function registerFeatures(): void
    {
        if ($this->features['database'])
        {
            $this->registerDataMapper();
        }

        if ($this->features['authentication'])
        {
            $this->registerAuthentication();
        }

        if ($this->features['engine'])
        {
            $this->registerEngine();
        }
    }

    /**
     * Register the data mapper ORM.
     */
    private function registerDataMapper(): void
    {
        $config = $this->readFeatureConfig('datamapper');

        $this->container->registerInstance('dataMapper', new DataMapper($config));
    }

    private function registerAuthentication(): void
    {
        $config = $this->readFeatureConfig('authentication');

        $this->container->registerInstance('authenticator', new Authenticator($config));
    }

    /**
     * Register the engine feature.
     */
    private function registerEngine(): void
    {
        if (!$this->features['database'])
        {
            throw new CoreException('Engine feature required database to be enabled.');
        }

        $schema = [];
        $schemaFile = $this->configPath.self::SCHEMA_DIRECTORY_NAME.'/'.self::SCHEMA_FILE_NAME;
        if (file_exists($schemaFile))
        {
            $schema = require $schemaFile;
        }

        $config = $this->readFeatureConfig('engine');

        $this->container->registerInstance('engine',
            new Engine($schema, $this->container->resolveInstance('dataMapper'), $this->container, $config));
    }

    /**
     * Register the router.
     */
    private function registerRouter(): void
    {
        $router = new Router($this->container);
        if ($this->features['validation'])
        {
            $router->enableValidation();
        }

        if ($this->features['engine'])
        {
            $router->enableEngine();
        }

        if ($this->features['middlewares'])
        {
            $router->enableMiddlewares();
        }

        $this->container->registerInstance('router', $router);
    }
}