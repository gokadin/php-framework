<?php

namespace Tests;

use Library\Testing\TestCase;

abstract class BaseTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        putenv('APP_ENV=framework_testing');

        require __DIR__.'/../Bootstrap/autoload.php';
    }
}