<?php

namespace Library\DataMapper\Database;

use Faker\Factory;
use Faker\Generator;
use Library\DataMapper\DataMapper;

abstract class Seeder
{
    /**
     * @var DataMapper
     */
    protected $dm;

    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * @param DataMapper $dm
     * @param Generator $faker
     */
    public function __construct(DataMapper $dm, Generator $faker)
    {
        $this->dm = $dm;
        $this->faker = $faker;
    }

    public function call($seederClass)
    {
        $seeder = new $seederClass($this->dm, $this->faker);

        $seeder->run();
    }

    abstract function run();
}