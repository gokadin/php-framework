<?php

namespace Tests\Library\DataMapper\Mapping\Drivers;

use Library\DataMapper\Mapping\Drivers\AnnotationDriver;
use Tests\BaseTest;

class AnnotationDriverTest extends BaseTest
{
    /**
     * @var AnnotationDriver
     */
    private $driver;

    public function setUp()
    {
        parent::setUp();

        $this->driver = new AnnotationDriver();
    }

    public function test_addSomeTests()
    {
        $this->assertTrue(false);
    }
}