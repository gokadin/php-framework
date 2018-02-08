<?php

namespace Tests\Library\Core;

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
}