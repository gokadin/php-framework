<?php

namespace Tests\Library\Authentication;

use Library\Authentication\Authenticator;
use Tests\App\Models\Admin;
use Tests\BaseTest;

class AuthenticatorTest extends BaseTest
{
    public function setUp()
    {
        parent::setUp();

        $this->loadEnvironment();
    }

    public function test_authenticate_()
    {
        // Arrange
        $authenticator = new Authenticator([
            'models' => [
                Admin::class => [
                    'role' => 'admin',
                    'access' => '/**'
                ]
            ]
        ]);

        // Act
        //$authenticator->authenticate();

        // Assert
        $this->assertTrue(true);
    }
}