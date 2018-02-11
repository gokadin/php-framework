<?php

namespace Library\Core;

use Library\Container\Container;
use Library\DataMapper\DataMapper;
use Library\Engine\Engine;
use Library\Routing\Router;

class AppConfigurator
{
    private const CONFIG_DIRECTORY_NAME = 'Config';
    private const FEATURES_FILE_NAME = 'features.php';
    private const DATAMAPPER_CONFIG_FILE_NAME = 'datamapper.php';
    private const ENGINE_CONFIG_FILE_NAME = 'engine.php';

    /**
     * @var Container
     */
    private $container;

    /**
     * @var string
     */
    private $configPath;

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
        $this->configPath = $basePath.'/'.self::CONFIG_DIRECTORY_NAME.'/';
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
        $configFile = $this->configPath.$featureName.'.php';
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
        $this->registerEssentials();
        $this->registerFeatures();
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

        $this->container->registerInstance('router', $router);
    }

    /**
     * Register the data mapper ORM.
     */
    private function registerDataMapper(): void
    {
        $config = $this->readFeatureConfig('datamapper');

        $this->container->registerInstance('dataMapper', new DataMapper($config));
    }

    /**
     * Register the engine feature.
     */
    private function registerEngine(): void
    {
        $config = $this->readFeatureConfig('engine');

        $this->container->registerInstance('engine', new Engine())
    }
}