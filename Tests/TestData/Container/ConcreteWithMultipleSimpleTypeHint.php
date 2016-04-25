<?php

namespace Tests\TestData\Container;

class ConcreteWithMultipleSimpleTypeHint
{
    protected $a;
    protected $b;
    protected $c;

    public function __construct(ConcreteNoConstructor $a, ConcreteNoConstructor $b, ConcreteNoConstructor $c)
    {
        $this->a = $a;
        $this->b = $b;
        $this->c = $c;
    }

    public function getA()
    {
        return $this->a;
    }

    public function getB()
    {
        return $this->b;
    }

    public function getC()
    {
        return $this->c;
    }
}