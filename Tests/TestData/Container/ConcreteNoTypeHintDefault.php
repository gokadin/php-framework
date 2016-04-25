<?php

namespace Tests\TestData\Container;

class ConcreteNoTypeHintDefault
{
    protected $a;

    public function __construct($a = 3)
    {
        $this->a = $a;
    }

    public function getA()
    {
        return $this->a;
    }
}