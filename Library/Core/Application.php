<?php

namespace Library\Core;

use Library\Container\Container;
use Library\Container\ContainerConfiguration;
use Library\Routing\RouteBuilder;

class Application
{
    /**
     * @var string
     */
    private $basePath;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var mixed
     */
    private $controllerResponse;

    /**
     * Initializes the framework.
     *
     * @param string $basePath
     */
    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;

        $this->loadEnvironment();

        $this->setUpContainer();
    }

    /**
     * @return string
     */
    public function basePath()
    {
        return $this->basePath;
    }

    /**
     * @return Container
     */
    public function container()
    {
        return $this->container;
    }

    /**
     * Process the request through the route.
     */
    public function processRoute()
    {
        $builder = new RouteBuilder();
        $routes = $builder->getRoutes();

        $router = $this->container->resolveInstance('router');
        $router->setRoutes($routes);

        $result = $this->container->resolveInstance('router')->dispatch(
            $this->container()->resolveInstance('request'));

        $this->controllerResponse = $result;
    }

    /**
     * Returns framework result to the client.
     */
    public function sendResponse()
    {
        $this->controllerResponse->executeResponse();
    }

    /**
     * Loads the environment variables.
     */
    private function loadEnvironment()
    {
        $environment = new Environment($this->basePath);
        $environment->load();
    }

    /**
     * Instanciates and configures the container.
     */
    private function setUpContainer()
    {
        $this->container = new Container();
        $this->container->registerInstance('app', $this);

        $containerConfiguration = new ContainerConfiguration($this->container, $this->basePath);
        $containerConfiguration->configure();
    }
}

