<?php

namespace Tests\TestData\Container;

class ConcreteWithMixedButValid
{
    protected $a;
    protected $b;
    protected $c;

    public function __construct(ConcreteWithMultipleComplexTypeHint $a, ConcreteNoTypeHintDefault $b, $c = 3)
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