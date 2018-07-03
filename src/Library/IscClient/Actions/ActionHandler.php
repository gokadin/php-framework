<?php

namespace Library\IscClient\Actions;

use Library\Container\Container;
use Library\IscClient\Controllers\IscController;
use Library\IscClient\IscClient;
use Library\IscClient\Subscriptions\SubscriptionRoute;

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

    public function handle(SubscriptionRoute $route, array $payload)
    {
        $methodParameters = $this->resolveMethodParameters($route->class(), $route->method());
        $controller = $this->resolveController($route, $payload);
        $this->executeAction($controller, $route->method(), $methodParameters);

        $this->sendDiagnosticInfo($route, $payload);
    }

    private function sendDiagnosticInfo(SubscriptionRoute $route, $payload)
    {
        if ($route->topic() == 'Pyramid.Diagnostics')
        {
            return;
        }

        $iscClient = $this->container->resolve(IscClient::class);
        $iscClient->dispatchEvent('Pyramid.Diagnostics', 'messageReceived', [
            'resourceName' => getenv('APP_NAME'),
            'topic' => $route->topic(),
            'type' => $route->type(),
            'action' => $route->action(),
            'payload' => $payload
        ]);
    }

    private function resolveController(SubscriptionRoute $route, array $payload): IscController
    {
        $controller = $this->container->resolve($route->class());
        $this->container->resolveObjectProperty($controller, 'payload', $payload);
        $this->container->resolveObjectProperty($controller, 'isc', $this->container->resolveInstance('isc'));
        $this->container->resolveObjectProperty($controller, 'route', $route);
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