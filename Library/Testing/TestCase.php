<?php

namespace Library\Testing;

use PHPUnit_Framework_TestCase;

class TestCase extends PHPUnit_Framework_TestCase
{
    protected $tearDownCallbacks = [];

    public function setUp()
    {
        require __DIR__.'/../Configuration/envFunctions.php';

        putenv('APP_ENV=testing');

        configureEnvironment();
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