<?php

namespace Tests\TestData\Container;

class ConcreteWithSimpleTypeHint
{
    protected $a;

    public function __construct(ConcreteNoConstructor $a)
    {
        $this->a = $a;
    }

    public function getA()
    {
        return $this->a;
    }
}