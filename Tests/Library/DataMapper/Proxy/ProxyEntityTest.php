<?php

namespace Tests\Library\DataMapper\Proxy;

use Library\DataMapper\Proxy\ProxyEntity;
use Tests\Library\DataMapper\DataMapperBaseTest;
use Tests\TestData\DataMapper\LazyEntityOne;
use Tests\TestData\DataMapper\LazyEntityTwo;

class ProxyEntityTest extends DataMapperBaseTest
{
    public function testHasOneLazyLoadedPropertyIsProxied()
    {
        // Arrange
        $this->setUpLazyEntities();
        $one = new LazyEntityOne('one');
        $two = new LazyEntityTwo('two', $one);
        $one->setEntityTwo($two);
        $this->dm->persist($one);
        $this->dm->persist($two);
        $this->dm->flush();
        $this->dm->detachAll();

        // Act
        $one = $this->dm->find(LazyEntityOne::class, $one->getId());

        // Assert
        $this->assertNotNull($one);
        $this->assertTrue($one->entityTwo() instanceof ProxyEntity);
    }

    public function testBelongsToLazyLoadedPropertyIsProxied()
    {
        // Arrange
        $this->setUpLazyEntities();
        $one = new LazyEntityOne('one');
        $two = new LazyEntityTwo('two', $one);
        $one->setEntityTwo($two);
        $this->dm->persist($one);
        $this->dm->persist($two);
        $this->dm->flush();
        $this->dm->detachAll();

        // Act
        $one = $this->dm->find(LazyEntityTwo::class, $one->getId());

        // Assert
        $this->assertNotNull($one);
        $this->assertTrue($one->entityOne() instanceof ProxyEntity);
    }

    /**
     * @depends testHasOneLazyLoadedPropertyIsProxied
     */
    public function testProxyIsResolvedWheneverAMethodIsCalled()
    {
        // Arrange
        $this->setUpLazyEntities();
        $one = new LazyEntityOne('one');
        $two = new LazyEntityTwo('two', $one);
        $one->setEntityTwo($two);
        $this->dm->persist($one);
        $this->dm->persist($two);
        $this->dm->flush();
        $this->dm->detachAll();

        // Act
        $one = $this->dm->find(LazyEntityOne::class, $one->getId());
        $name = $one->entityTwo()->name();

        // Assert
        $this->assertTrue($one->entityTwo() instanceof LazyEntityTwo);
        $this->assertEquals($two->name(), $name);
    }

    /**
     * @depends testHasOneLazyLoadedPropertyIsProxied
     */
    public function testProxyIsResolvedWheneverAPropertyIsAccessed()
    {
        // Arrange
        $this->setUpLazyEntities();
        $one = new LazyEntityOne('one');
        $two = new LazyEntityTwo('two', $one);
        $one->setEntityTwo($two);
        $this->dm->persist($one);
        $this->dm->persist($two);
        $this->dm->flush();
        $this->dm->detachAll();

        // Act
        $one = $this->dm->find(LazyEntityOne::class, $one->getId());
        $publicProp = $one->entityTwo()->publicProp;

        // Assert
        $this->assertTrue($one->entityTwo() instanceof LazyEntityTwo);
        $this->assertEquals($two->publicProp, $publicProp);
    }

    /**
     * @depends testHasOneLazyLoadedPropertyIsProxied
     */
    public function testHasOneProxyIsResolvedWhenRequestingTheId()
    {
        // Arrange
        $this->setUpLazyEntities();
        $one = new LazyEntityOne('one');
        $two = new LazyEntityTwo('two', $one);
        $one->setEntityTwo($two);
        $this->dm->persist($one);
        $this->dm->persist($two);
        $this->dm->flush();
        $this->dm->detachAll();

        // Act
        $one = $this->dm->find(LazyEntityOne::class, $one->getId());
        $id = $one->entityTwo()->getId();

        // Assert
        $this->assertTrue($one->entityTwo() instanceof LazyEntityTwo);
        $this->assertEquals($two->getId(), $id);
    }

    /**
     * @depends testHasOneLazyLoadedPropertyIsProxied
     */
    public function testBelongsToProxyIsNotResolvedWhenRequestingTheId()
    {
        // Arrange
        $this->setUpLazyEntities();
        $one = new LazyEntityOne('one');
        $two = new LazyEntityTwo('two', $one);
        $one->setEntityTwo($two);
        $this->dm->persist($one);
        $this->dm->persist($two);
        $this->dm->flush();
        $this->dm->detachAll();

        // Act
        $two = $this->dm->find(LazyEntityTwo::class, $two->getId());
        $id = $two->entityOne()->getId();

        // Assert
        $this->assertTrue($two->entityOne() instanceof ProxyEntity);
        $this->assertEquals($one->getId(), $id);
    }
}