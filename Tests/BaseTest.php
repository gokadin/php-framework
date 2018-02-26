<?php

namespace Tests;

use Library\Core\Environment;
use PHPUnit\Framework\TestCase;

abstract class BaseTest extends TestCase
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