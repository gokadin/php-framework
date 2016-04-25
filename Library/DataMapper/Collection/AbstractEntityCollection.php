<?php

namespace Library\DataMapper\Collection;

use IteratorAggregate;
use JsonSerializable;

abstract class AbstractEntityCollection implements IteratorAggregate, JsonSerializable
{
    protected $count;

    public function __construct()
    {
        $this->count = 0;
    }

    abstract function add($value);

    abstract function remove($value);

    abstract function first();

    abstract function last();

    abstract function at($index);

    abstract function toArray();

    abstract function slice($offset, $length = null);

    public function count()
    {
        return $this->count;
    }

    public function isEmpty()
    {
        return $this->count == 0;
    }
}