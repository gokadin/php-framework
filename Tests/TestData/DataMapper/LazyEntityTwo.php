<?php

namespace Tests\TestData\DataMapper;

use Library\DataMapper\DataMapperPrimaryKey;

/** @Entity */
class LazyEntityTwo
{
    use DataMapperPrimaryKey;

    public $publicProp = 'public';

    /** @Column(type="string") */
    private $name;

    /** @BelongsTo(target="Tests\TestData\DataMapper\LazyEntityOne") */
    private $entityOne;

    public function __construct($name, LazyEntityOne $entityOne = null)
    {
        $this->name = $name;
        $this->entityOne = $entityOne;
    }

    public function name()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function entityOne()
    {
        return $this->entityOne;
    }

    public function setEntityOne(LazyEntityOne $entityOne)
    {
        $this->entityOne = $entityOne;
    }
}