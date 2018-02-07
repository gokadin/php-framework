<?php

namespace Tests\Library\Container;

use Tests\BaseTest;
use Library\Container\Container;
use Library\Container\ContainerConfiguration;

class ContainerConfigurationTest extends BaseTest
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var ContainerConfiguration
     */
    private $containerConfiguration;

    public function setUp()
    {
        parent::setUp();

        $this->container = new Container();
        $this->containerConfiguration = new ContainerConfiguration($this->container, $this->basePath());
    }

    public function test_configure_registersAllTheEssentialClasses()
    {
        // Act
        $this->containerConfiguration->configure();

        // Assert
        $this->assertNotNull($this->container->resolveInstance('request'));
    }
}