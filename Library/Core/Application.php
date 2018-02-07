<?php

namespace Library\Core;

use Library\Container\Container;
use Library\Container\ContainerConfiguration;
use Library\Routing\RouteBuilder;
use Library\Routing\RouteCollection;

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
    private $response;

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
     * Loads the environment variables.
     */
    private function loadEnvironment(): void
    {
        $environment = new Environment($this->basePath);
        $environment->load();
    }

    /**
     * Instanciates and configures the container.
     */
    private function setUpContainer(): void
    {
        $this->container = new Container();
        $this->container->registerInstance('app', $this);

        $containerConfiguration = new ContainerConfiguration($this->container, $this->basePath);
        $containerConfiguration->configure();
    }

    /**
     * @return string
     */
    public function basePath(): string
    {
        return $this->basePath;
    }

    /**
     * @return Container
     */
    public function container(): Container
    {
        return $this->container;
    }

    /**
     * Process the request through the route.
     */
    public function processRoute(): void
    {
        $router = $this->container->resolveInstance('router');
        $request = $this->container->resolveInstance('request');

        $this->response = $router->dispatch($this->buildRoutes(), $request);
    }

    /**
     * @return RouteCollection
     */
    private function buildRoutes(): RouteCollection
    {
        $builder = new RouteBuilder($this->basePath);
        return $builder->getRoutes();
    }

    /**
     * Returns framework result to the client.
     */
    public function sendResponse(): void
    {
        $this->response->executeResponse();
    }
}

