<?php

namespace Tests\TestData\Container;

class ConcreteWithProperties
{
    public $publicPropOne;

    private $privatePropOne;

    public function privatePropOne()
    {
        return $this->privatePropOne;
    }
}