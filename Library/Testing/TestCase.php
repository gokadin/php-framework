<?php

namespace Library\Testing;

use PHPUnit_Framework_TestCase;
use Mockery;

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