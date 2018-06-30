<?php

namespace Tests\Library\Core;

use Library\Authentication\Authenticator;
use Library\DataMapper\DataMapper;
use Library\Engine\Engine;
use Library\IscClient\IscClient;
use Library\Routing\Router;
use Tests\BaseTest;
use Library\Container\Container;
use Library\Core\AppConfigurator;

class AppConfiguratorTest extends BaseTest
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var AppConfigurator
     */
    private $appConfigurator;

    public function setUp()
    {
        parent::setUp();

        $this->loadEnvironment();

        $this->container = new Container();
        $this->appConfigurator = new AppConfigurator($this->container, $this->basePath());
    }

    public function test_configure_registersAllTheEssentialClasses()
    {
        // Act
        $this->appConfigurator->configure();

        // Assert
        $this->assertNotNull($this->container->resolve(Router::class));
    }

    public function test_configure_registerDataMapperWhenEnabled()
    {
        // Act
        $this->appConfigurator->configure();

        // Assert
        $this->assertNotNull($this->container->resolve(DataMapper::class));
    }

    public function test_configure_registerEngineWhenEnabled()
    {
        // Act
        $this->appConfigurator->configure();

        // Assert
        $this->assertNotNull($this->container->resolve(Engine::class));
    }

    public function test_configure_registerAuthenticationWhenEnabled()
    {
        // Act
        $this->appConfigurator->configure();

        // Assert
        $this->assertNotNull($this->container->resolve(Authenticator::class));
    }

    public function test_configure_registerIscWhenEnabled()
    {
        // Act
        $this->appConfigurator->configure();

        // Assert
        $this->assertNotNull($this->container->resolve(IscClient::class));
    }
}