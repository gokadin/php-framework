<?php

namespace Tests;

use PHPUnit_Framework_TestCase;

abstract class BaseTest extends PHPUnit_Framework_TestCase
{
    protected function basePath()
    {
        return realpath(__DIR__);
    }
}