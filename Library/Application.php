<?php

namespace Library;

use Library\Container\Container;
use Library\Container\ContainerConfiguration;
use Library\Http\Response;
use Library\Http\View;
use Library\Http\ViewFactory;
use Library\Routing\Router;

class Application
{
    /**
     * @var string
     */
    protected $basePath;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var mixed
     */
    protected $controllerResponse;

    /**
     * @var Router
     */
    private $router;

    public function __construct()
    {
        $this->configureErrorHandling();

        $this->container = new Container();
        $this->controllerResponse = null;
        $this->basePath = __DIR__.'/../';

        $this->configureContainer();

        $this->router = $this->container->resolveInstance('router');
    }

    private function configureErrorHandling()
    {
        switch (env('APP_DEBUG'))
        {
            case 'true':
                error_reporting(E_ALL);
                break;
            default:
                error_reporting(0);
                break;
        }
    }

    protected function configureContainer()
    {
        $this->container->registerInstance('app', $this);
        $containerConfiguration = new ContainerConfiguration($this->container);
        $containerConfiguration->configureContainer();

        $appContainerConfiguration = new \Config\ContainerConfiguration($this->container);
        $appContainerConfiguration->configureContainer();
    }

    public function container()
    {
        if (is_null($this->container))
        {
            $this->container = new Container();
            $this->ConfigureContainer();
        }

        return $this->container;
    }

    public function loadRoutes()
    {
        $router = $this->router;
        $router->group(['namespace' => 'App\\Http\\Controllers'], function($router) {
            require __DIR__ . '/../App/Http/routes.php';
        });
    }

    public function processRoute()
    {
        $result = $this->router->dispatch($this->container()->resolveInstance('request'));

        $this->controllerResponse = $result;
    }

    /**
     * Takes the application output and sends it to
     * the client.
     * Allowed output types are:
     * -> strings
     * -> View object
     * -> Response object
     *
     * @throws Container\ContainerException
     */
    // TODO: add error output if nothing valid is found
    public function sendView()
    {
        if ($this->controllerResponse instanceof Response)
        {
            $this->controllerResponse->executeResponse();
        }

        if ($this->controllerResponse instanceof View)
        {
            $content = $this->controllerResponse->processView(new ViewFactory($this->container),
                $this->container->resolveInstance('shao'));

            echo $content;
            exit();
        }

        if (is_string($this->controllerResponse))
        {
            echo $this->controllerResponse;
            exit();
        }
    }

    public function basePath()
    {
        return $this->basePath;
    }
}
