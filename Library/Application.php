<?php

namespace Library;

use Config\EventRegistration;
use Library\Container\Container;
use Library\Container\ContainerConfiguration;
use Library\Facades\Facade;
use Library\Http\Response;
use Library\Http\View;
use Library\Http\ViewFactory;
use Library\Routing\Router;

class Application
{
    protected $basePath;
    protected $container;
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

        $this->loadRoutes();
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
        if ($this->container == null)
        {
            $this->container = new Container();
            $this->ConfigureContainer();
        }

        return $this->container;
    }

    private function loadRoutes()
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

    public function sendView()
    {
        if (!is_object($this->controllerResponse) && !is_array($this->controllerResponse))
        {
            echo $this->controllerResponse;
            exit();
            return;
        }

        if (is_array($this->controllerResponse))
        {
            echo json_encode($this->controllerResponse);
            exit();
            return;
        }

        if ($this->controllerResponse instanceof View)
        {
            $content = $this->controllerResponse->processView(new ViewFactory($this->container),
                $this->container->resolveInstance('shao'));

            echo $content;
            exit();
            return;
        }

        if ($this->controllerResponse instanceof Response)
        {
            $this->controllerResponse->executeResponse();
        }
    }

    public function basePath()
    {
        return $this->basePath;
    }
}
