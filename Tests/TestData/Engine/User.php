<?php

namespace Tests\TestData\Engine;

use Library\DataMapper\DataMapperPrimaryKey;
use Library\DataMapper\DataMapperTimestamps;

/** @Entity */
class User
{
    use DataMapperPrimaryKey, DataMapperTimestamps;

    /** @Column(type="string") */
    private $name;

    /** @Column(type="integer") */
    private $age;

    public function __construct(string $name, int $age)
    {
        $this->name = $name;
        $this->age = $age;
    }

    public function name()
    {
        return $this->name;
    }

    public function age()
    {
        return $this->age;
    }
}