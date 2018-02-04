<?php

namespace Library\Container;

use Library\Http\Request;

class ContainerConfiguration
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var string
     */
    private $basePath;

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
    }

    /**
     * Configures the container with all of the framework
     * classes plus the ones defined by the user.
     */
    public function configure()
    {
        $this->registerEssentials();
    }

    /**
     * Registers essential classes needed by the framework.
     */
    private function registerEssentials()
    {
        $this->container->registerInstance('request', new Request());
    }
}