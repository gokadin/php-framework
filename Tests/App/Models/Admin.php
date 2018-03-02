<?php

namespace Tests\App\Models;
use Library\DataMapper\DataMapperPrimaryKey;
use Library\DataMapper\DataMapperTimestamps;

/** @Entity */
class Admin
{
    use DataMapperPrimaryKey, DataMapperTimestamps;

    /** @Column(type="string") */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
}