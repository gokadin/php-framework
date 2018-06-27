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
        $methodParameters = $this->resolveMethodParameters($route['class'], $route['method']);
        $controller = $this->resolveController($route['class'], $payload);
        $this->executeAction($controller, $route['method'], $methodParameters);

        $this->sendDiagnosticInfo($route, $payload);
    }

    private function sendDiagnosticInfo($route, $payload)
    {
        if ($route['topic'] == 'Pyramid.Diagnostics')
        {
            return;
        }

        $iscClient = $this->container->resolve(IscClient::class);
        $iscClient->dispatchEvent('Pyramid.Diagnostics', 'messageReceived', [
            'resourceName' => getenv('APP_NAME'),
            'topic' => $route['topic'],
            'type' => $route['type'],
            'action' => $route['action'],
            'payload' => $payload
        ]);
    }

    private function resolveController(string $class, array $payload): IscController
    {
        $controller = $this->container->resolve($class);
        $this->container->resolveObjectProperty($controller, 'payload', $payload);
        $this->container->resolveObjectProperty($controller, 'isc', IscClient::class);
        return $controller;
    }

    private function resolveMethodParameters(string $class, string $method)
    {
        return $this->container->resolveMethodParameters($class, $method);
    }

    private function executeAction(IscController $controller, string $method, array $methodParameters)
    {
        call_user_func_array([$controller, $method], $methodParameters);
    }
}