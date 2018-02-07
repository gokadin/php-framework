<?php

namespace Tests\TestData\Container;

class ConcreteWithDefaultInterface
{
    protected $a;

    public function __construct(InterfaceOne $a = null)
    {
        $this->a = $a;
    }

    public function getA()
    {
        return $this->a;
    }
}