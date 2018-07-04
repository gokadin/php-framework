<?php

namespace Tests\Library\IscClient\Subscriptions;

use Library\IscClient\IscConstants;
use Library\IscClient\Subscriptions\SubscriptionDiscovery;
use Tests\BaseTest;

class SubscriptionDiscoveryTest extends BaseTest
{
    /**
     * @var SubscriptionDiscovery
     */
    private $subscriptionDiscovery;

    public function setUp()
    {
        parent::setUp();

        $this->loadEnvironment();

        $this->subscriptionDiscovery = new SubscriptionDiscovery($this->basePath(), 'App/Isc');
    }

    public function test_findSubscriptionRoute_correctlyLoadsSimpleEventRoute()
    {
        // Act
        $route = $this->subscriptionDiscovery->findSubscriptionRoute('Accounts', IscConstants::EVENT_TYPE, 'accountCreated');

        // Assert
        $this->assertNotNull($route);
        $this->assertEquals('Tests\\App\\Isc\\Accounts\\AccountEvents', $route->class());
        $this->assertEquals('onAccountCreated', $route->method());
    }

    public function test_findSubscriptionRoute_correctlyLoadsNestedTopicNameRoute()
    {
        // Act
        $route = $this->subscriptionDiscovery->findSubscriptionRoute('Accounts.SubTopic', IscConstants::EVENT_TYPE, 'subAccountCreated');

        // Assert
        $this->assertNotNull($route);
        $this->assertEquals('Tests\\App\\Isc\\Accounts\\SubTopic\\SubTopicEvents', $route->class());
        $this->assertEquals('onSubAccountCreated', $route->method());
    }

    public function test_getSubscriptionStrings_forSimpleEvent()
    {
        // Act
        $strings = $this->subscriptionDiscovery->getSubscriptionStrings();

        // Assert
        $this->assertGreaterThan(0, sizeof($strings));
        $this->assertTrue(in_array('Accounts.EVENT.accountCreated.*', $strings));
    }

    public function test_getSubscriptionStrings_forNestedTopicName()
    {
        // Act
        $strings = $this->subscriptionDiscovery->getSubscriptionStrings();

        // Assert
        $this->assertGreaterThan(0, sizeof($strings));
        $this->assertTrue(in_array('Accounts.SubTopic.EVENT.subAccountCreated.*', $strings));
    }
}