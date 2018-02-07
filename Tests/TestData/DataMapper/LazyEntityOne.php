<?php

namespace Tests\TestData\DataMapper;

use Library\DataMapper\DataMapperPrimaryKey;

/** @Entity */
class LazyEntityOne
{
    use DataMapperPrimaryKey;

    public $publicProp = 'public';

    /** @Column(type="string") */
    private $name;

    /** @HasOne(target="Tests\TestData\DataMapper\LazyEntityTwo") */
    private $entityTwo;

    public function __construct($name, LazyEntityTwo $entityTwo = null)
    {
        $this->name = $name;
        $this->entityTwo = $entityTwo;
    }

    public function name()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function entityTwo()
    {
        return $this->entityTwo;
    }

    public function setEntityTwo(LazyEntityTwo $entityTwo)
    {
        $this->entityTwo = $entityTwo;
    }
}