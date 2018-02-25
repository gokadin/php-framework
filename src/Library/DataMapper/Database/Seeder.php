<?php

namespace Library\DataMapper\Database;

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

    abstract function run();

    protected function call(string $class, int $times = 1)
    {
        $instance = new $class($this->dm, $this->faker);

        for ($i = 0; $i < $times; $i++)
        {
            $this->dm->persist($instance->run());
        }
    }
}