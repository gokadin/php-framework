<?php

namespace Tests\DataMapper\Mapping\Drivers;

use Library\DataMapper\Mapping\Drivers\AnnotationDriver;
use Tests\BaseTest;
use Tests\TestData\DataMapper\Mapping\AnnotationClasses\AssocEntity;

class AnnotationDriverTest extends BaseTest
{
    /**
     * @var AnnotationDriver
     */
    private $driver;

    public function setUpDriver()
    {
        $this->driver = new AnnotationDriver();
    }
}