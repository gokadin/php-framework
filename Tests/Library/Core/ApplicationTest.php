<?php

namespace Tests\Library\Core;

use Library\Core\Environment;
use Tests\BaseTest;
use Library\Core\Application;

class ApplicationTest extends BaseTest
{
    /**
     * @var Application
     */
    private $app;

    public function setUp()
    {
        parent::setUp();

        $this->app = new Application($this->basePath());
    }

    public function test_ctor_basePathIsSetCorrectly()
    {
        // Assert
        $this->assertEquals($this->basePath(), $this->app->basePath());
    }

    public function test_ctor_environmentsAreCorrectlyLoaded()
    {
        // Assert
        $this->assertEquals('test', getenv(Environment::APP_ENV_KEY));
        $this->assertEquals('test', getenv('DUMMY'));
    }

    public function test_ctor_containerIsNotNull()
    {
        // Assert
        $this->assertNotNull($this->app->container());
    }

    public function test_ctor_containerContainsApp()
    {
        // Act
        $app = $this->app->container()->resolveInstance('app');

        // Assert
        $this->assertNotNull($app);
        $this->assertEquals($this->basePath(), $app->basePath());
    }
}