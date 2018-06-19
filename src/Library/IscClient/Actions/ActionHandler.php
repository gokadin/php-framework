<?php

namespace Library\IscClient\Actions;

use Library\Container\Container;
use Library\IscClient\Controllers\IscController;

class ActionHandler
{
    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function handle(array $route, array $payload)
    {
        $methodParameters = $this->resolveMethodParameters($route['class'], $route['method'], $payload);
        $controller = $this->resolveController($route['class']);
        $this->executeAction($controller, $route['method'], $methodParameters);
    }

    private function resolveController(string $class): IscController
    {
        $this->container->resolve($class);
    }

    private function resolveMethodParameters(string $class, string $method, array $payload)
    {
        return $this->container->resolveMethodParameters($class, $method, [
            'event' => $payload,
            'command' => $payload,
            'query' => $payload
        ]);
    }

    private function executeAction(IscController $controller, string $method, array $methodParameters)
    {
        call_user_func_array([$controller, $method], $methodParameters);
    }
}