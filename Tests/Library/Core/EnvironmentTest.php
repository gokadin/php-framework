<?php

namespace Tests\Library\Core;

use Library\Core\Environment;
use Tests\BaseTest;

class EnvironmentTest extends BaseTest
{
    /**
     * @var Environment
     */
    private $environment;

    public function setUp()
    {
        parent::setUp();

        $this->environment = new Environment($this->basePath());
    }

    public function test_load_loadsVariablesCorrectly()
    {
        // Act
        $this->environment->load();

        // Assert
        $this->assertEquals('test', getenv(Environment::APP_ENV_KEY));
        $this->assertEquals('test', getenv('DUMMY'));
    }
}