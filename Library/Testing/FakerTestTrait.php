<?php

namespace Library\Testing;

use Faker\Factory;

trait FakerTestTrait
{
    /**
     * @var Faker
     */
    protected $faker;

    private function setUpFaker()
    {
        $this->faker = Factory::create();
    }
}