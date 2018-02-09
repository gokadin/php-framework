<?php

namespace Tests;

use Library\Core\Environment;
use PHPUnit_Framework_TestCase;

abstract class BaseTest extends PHPUnit_Framework_TestCase
{
    protected function basePath()
    {
        return realpath(__DIR__);
    }

    protected function loadEnvironment()
    {
        $environment = new Environment($this->basePath());
        $environment->load();
    }
}