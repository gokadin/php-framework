<?php

namespace Library\Core;

use Library\Container\Container;
use Library\Http\Request;
use Library\Routing\Router;

class AppConfigurator
{
    private const CONFIG_DIRECTORY_NAME = 'Config';
    private const FEATURES_FILE_NAME = 'features.php';

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
        $this->readConfiguration();

        $this->setUpContainer();
    }

    /**
     * Read all configuration files necessary to set up the container.
     *
     * @throws CoreException
     */
    private function readConfiguration()
    {
        $file = $this->configPath.self::FEATURES_FILE_NAME;
        if (!file_exists($file))
        {
            throw new CoreException('Could not find features configuration file.');
        }

        $this->features = require $file;
    }

    /**
     * Register all necessary classes in the container
     * to be resolved later.
     */
    private function setUpContainer(): void
    {
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
}