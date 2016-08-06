<?php

namespace Library;

use Library\Container\Container;
use Library\Http\Response;
use Library\Http\View;
use Library\Http\ViewFactory;

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
    private $controllerResponse;

    public function __construct()
    {
        $this->configureErrorHandling();

        $this->container = new Container();
        $this->controllerResponse = null;
        $this->basePath = __DIR__.'/../';
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

    public function configureContainer()
    {
        $this->container->registerInstance('app', $this);

        $containerConfiguration = null;
        switch (env('APP_ENV'))
        {
            case 'framework_testing':
                $containerConfiguration = new \Config\TestContainerConfiguration($this->container);
                break;
            default:
                $containerConfiguration = new \Config\ContainerConfiguration($this->container);
        }

        $containerConfiguration->configureContainer();
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
        $router = $this->container->resolve('router');
        $router->group(['namespace' => 'App\\Http\\Controllers'], function($router) {
            require __DIR__ . '/../App/Http/routes.php';
        });
    }

    public function processRoute()
    {
        $result = $this->container->resolve('router')->dispatch(
            $this->container()->resolveInstance('request'));

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
