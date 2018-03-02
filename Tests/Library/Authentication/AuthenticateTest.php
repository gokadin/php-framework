<?php

namespace Tests\Library\Authentication;

use Library\Authentication\Authenticate;
use Library\Authentication\Authenticator;
use Library\Container\Container;
use Library\Http\Request;
use Library\Http\Response;
use Tests\App\Models\Admin;

class AuthenticateTest extends AuthenticationBaseTest
{
    /**
     * @var Authenticate
     */
    private $middleware;

    /**
     * @var Container
     */
    private $container;

    public function setUp()
    {
        parent::setUp();

        $this->setUpAdmin();

        $this->loadEnvironment();

        $authenticator = new Authenticator([
            'models' => [
                Admin::class => [
                    'role' => 'admin',
                    'access' => '/**'
                ]
            ]
        ]);
        $this->middleware = new Authenticate($authenticator);
        $this->container = new Container();
    }

    private function setRequest(Request $request)
    {
        $this->container->resolveObjectProperty($this->middleware, 'request', $request);
    }

    public function test_handle_returnsUnauthorizedIfRequestHasNoAuthorizationHeader()
    {
        // Arrange
        $request = new Request('GET', '/', [], [], []);
        $this->setRequest($request);

        // Act
        $result = $this->middleware->handle();

        // Assert
        $this->assertTrue($result instanceof Response);
        $this->assertEquals(Response::STATUS_UNAUTHORIZED, $result->statusCode());
    }

    public function test_handle_returnsUnauthorizedIfAuthorizationHeaderFormatIsIncorrect()
    {
        // Arrange
        $request = new Request('GET', '/', [], [], [
            'Authorization' => 'rubbish'
        ]);
        $this->setRequest($request);

        // Act
        $result = $this->middleware->handle();

        // Assert
        $this->assertTrue($result instanceof Response);
        $this->assertEquals(Response::STATUS_UNAUTHORIZED, $result->statusCode());
    }
}