<?php

namespace Library\Container;

abstract class ContainerConfiguration
{
    /**
     * @var Container
     */
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    abstract public function configureContainer();
}