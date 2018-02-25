<?php

namespace Library\Core;

use Library\Container\Container;
use Library\Routing\RouteBuilder;
use Library\Routing\RouteCollection;
use Library\Routing\Router;

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

        $appConfigurator = new AppConfigurator($this->container, $this->basePath);
        $appConfigurator->configure();
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
        $router = $this->container->resolve(Router::class);

        $this->response = $router->dispatch($this->buildRoutes());
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

