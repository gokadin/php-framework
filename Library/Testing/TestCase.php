<?php

namespace Library\Testing;

use Library\Facades\Request;
use Library\Facades\Router;
use Library\Facades\Session;
use PHPUnit_Framework_TestCase;
use Mockery;
use Library\Facades\Facade;

class TestCase extends PHPUnit_Framework_TestCase
{
    protected $tearDownCallbacks = [];

    public function setUp()
    {
        putenv('APP_ENV=testing');
    }

    public function tearDown()
    {
        foreach ($this->tearDownCallbacks as $callback)
        {
            call_user_func($callback);
        }
    }

    protected function addTearDownCallback(callable $callback)
    {
        $this->tearDownCallbacks[] = $callback;
    }
}