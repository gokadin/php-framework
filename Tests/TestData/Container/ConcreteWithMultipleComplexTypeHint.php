<?php

namespace Tests\TestData\Container;

class ConcreteWithMultipleComplexTypeHint
{
    protected $a;
    protected $b;

    public function __construct(ConcreteWithSimpleTypeHint $a, ConcreteWithMultipleSimpleTypeHint $b)
    {
        $this->a = $a;
        $this->b = $b;
    }

    public function getA()
    {
        return $this->a;
    }

    public function getB()
    {
        return $this->b;
    }
}