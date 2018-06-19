<?php

namespace Library\IscClient\Actions;

use Library\Container\Container;
use Library\IscClient\Controllers\IscController;
use Library\IscClient\IscClient;

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

        $iscClient = $this->container->resolve(IscClient::class);
        $iscClient->dispatchEvent('Pyramid.Diagnostics', 'messageReceived', [
            'resourceName' => getenv('APP_NAME'),
            'topic' => $route['topic'],
            'type' => $route['type'],
            'action' => $route['action'],
            'payload' => $payload
        ]);
    }

    private function resolveController(string $class): IscController
    {
        return $this->container->resolve($class);
    }

    private function resolveMethodParameters(string $class, string $method, array $payload)
    {
        return $this->container->resolveMethodParameters($class, $method, ['payload' => $payload]);
    }

    private function executeAction(IscController $controller, string $method, array $methodParameters)
    {
        call_user_func_array([$controller, $method], $methodParameters);
    }
}