<?php

namespace Tests\TestData\Container;

class ConcreteWithMethodParameters
{
    public function noParameters()
    {
        return true;
    }

    public function withDefault($a = 3)
    {
        return $a;
    }

    public function withoutDefault($a)
    {
        return $a;
    }

    public function withConcrete(ConcreteNoConstructor $a)
    {
        return $a;
    }

    public function withInterface(InterfaceOne $a)
    {
        return $a;
    }

    public function withDefaultInterface(InterfaceOne $a = null)
    {
        return $a;
    }
}