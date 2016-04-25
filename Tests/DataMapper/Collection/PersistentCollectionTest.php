<?php

namespace Tests\DataMapper\Collection;

use Library\DataMapper\Collection\PersistentCollection;
use Tests\DataMapper\DataMapperBaseTest;
use Tests\TestData\DataMapper\SimpleEntity;
use Exception;

class PersistentCollectionTest extends DataMapperBaseTest
{
    public function testAddingItemsThroughTheConstructorDoesNotAffectTheStateOfTheCollection()
    {
        // Arrange
        $this->setUpSimpleEntity();

        // Act
        $collection = new PersistentCollection($this->dm, SimpleEntity::class, [
            1, 2, 3
        ]);

        // Assert
        $this->assertEquals(3, $collection->count());
    }

    public function testAddingNonPersistedItemsCorrectlyChangesState()
    {
        // Arrange
        $this->setUpSimpleEntity();
        $collection = new PersistentCollection($this->dm, SimpleEntity::class);
        $e1 = new SimpleEntity(1, 2, 'one', 'two');
        $e2 = new SimpleEntity(1, 2, 'one', 'two');

        // Act
        $collection->add($e1);
        $collection->add($e2);

        // Assert
        $this->assertEquals(0, $collection->count());
    }

    public function testAddingPersistedItemsCorrectlyChangesState()
    {
        // Arrange
        $this->setUpSimpleEntity();
        $collection = new PersistentCollection($this->dm, SimpleEntity::class);
        $e1 = new SimpleEntity(1, 2, 'one', 'two');
        $e2 = new SimpleEntity(1, 2, 'one', 'two');
        $this->dm->persist($e1);
        $this->dm->persist($e2);
        $this->dm->flush();

        // Act
        $collection->add($e1);
        $collection->add($e2);

        // Assert
        $this->assertEquals(2, $collection->count());
    }

    public function testRemovingItemsCorrectlyChangesState()
    {
        // Arrange
        $this->setUpSimpleEntity();
        $collection = new PersistentCollection($this->dm, SimpleEntity::class);
        $e1 = new SimpleEntity(1, 2, 'one', 'two');
        $e2 = new SimpleEntity(1, 2, 'one', 'two');

        // Act
        $collection->add($e1);
        $collection->add($e2);
        $collection->remove($e1);

        // Assert
        $this->assertEquals(0, $collection->count());
    }

    public function testRemovingItemsCorrectlyChangesStateWhenItemsWereFirstAddedInConstructor()
    {
        // Arrange
        $this->setUpSimpleEntity();
        $e1 = new SimpleEntity(1, 2, 'one', 'two');
        $e2 = new SimpleEntity(1, 2, 'one', 'two');
        $e3 = new SimpleEntity(1, 2, 'one', 'two');
        $this->dm->persist($e1);
        $this->dm->persist($e2);
        $this->dm->persist($e3);
        $this->dm->flush();
        $collection = new PersistentCollection($this->dm, SimpleEntity::class, [
            $e1, $e2, $e3 // simulates adding with corresponding ids
        ]);

        // Act
        $collection->remove($e1);

        // Assert
        $this->assertEquals(2, $collection->count());
    }

    public function testThatWhenRequestingUnloadedItemItLoadsItCorrectly()
    {
        // Arrange
        $this->setUpSimpleEntity();
        $se = new SimpleEntity(1, 2, 'one', 'two');
        $this->dm->persist($se);
        $this->dm->flush();
        $collection = new PersistentCollection($this->dm, SimpleEntity::class, [$se->getId() => null]);

        // Act
        $loadedSe = $collection->first();

        // Assert
        $this->assertTrue($loadedSe instanceof SimpleEntity);
        $this->assertEquals($se->getId(), $loadedSe->getId());
    }

    public function testThatWhenRequestingArrayItLoadsAllItemsCorrectly()
    {
        // Arrange
        $this->setUpSimpleEntity();
        $se1 = new SimpleEntity(1, 2, 'one', 'two');
        $se2 = new SimpleEntity(1, 2, 'one', 'two');
        $se3 = new SimpleEntity(1, 2, 'one', 'two');
        $this->dm->persist($se1);
        $this->dm->persist($se2);
        $this->dm->persist($se3);
        $this->dm->flush();
        $collection = new PersistentCollection($this->dm, SimpleEntity::class, [
            $se1->getId() => null,
            $se2->getId() => null,
            $se3->getId() => null,
        ]);

        // Act
        $loadedArray = $collection->toArray();

        // Assert
        $this->assertEquals(3, sizeof($loadedArray));
        foreach ($loadedArray as $loadedSe)
        {
            $this->assertTrue($loadedSe instanceof SimpleEntity);
        }
    }

    public function testThatWhenRequestingIteratorItLoadsAllItemsCorrectly()
    {
        // Arrange
        $this->setUpSimpleEntity();
        $se1 = new SimpleEntity(1, 2, 'one', 'two');
        $se2 = new SimpleEntity(1, 2, 'one', 'two');
        $se3 = new SimpleEntity(1, 2, 'one', 'two');
        $this->dm->persist($se1);
        $this->dm->persist($se2);
        $this->dm->persist($se3);
        $this->dm->flush();
        $collection = new PersistentCollection($this->dm, SimpleEntity::class, [
            $se1->getId() => null,
            $se2->getId() => null,
            $se3->getId() => null,
        ]);

        // Assert
        $counter = 0;
        foreach ($collection as $loadedSe)
        {
            $this->assertTrue($loadedSe instanceof SimpleEntity);
            $counter++;
        }
        $this->assertEquals(3, $counter);
    }

    public function testJsonEncodeWorksCorrectly()
    {
        // Arrange
        $this->setUpSimpleEntity();
        $se1 = new SimpleEntity(1, 2, 'one', 'two');
        $se2 = new SimpleEntity(1, 2, 'one', 'two');
        $se3 = new SimpleEntity(1, 2, 'one', 'two');
        $this->dm->persist($se1);
        $this->dm->persist($se2);
        $this->dm->persist($se3);
        $this->dm->flush();
        $collection = new PersistentCollection($this->dm, SimpleEntity::class, [
            $se1->getId() => null,
            $se2->getId() => null,
            $se3->getId() => null,
        ]);

        $encoded = json_encode($collection);
        $decoded = json_decode($encoded, true);

        // Assert
        $this->assertEquals(3, sizeof($decoded));
    }

    public function testGetIdList()
    {
        // Arrange
        $this->setUpSimpleEntity();
        $ids = [];
        $entities = [];
        $firstHalf = [];
        $secondHalf = [];
        for ($i = 0; $i < 100; $i++)
        {
            $entity = new SimpleEntity($i, 2, 'one', 'two');
            $entities[] = $entity;
            $this->dm->persist($entity);
        }
        $this->dm->flush();
        for ($i = 0; $i < 100; $i++)
        {
            $ids[] = $entities[$i]->getId();

            if ($i < 50)
            {
                $firstHalf[$entities[$i]->getId()] = null;
                continue;
            }

            $secondHalf[] = $entities[$i];
        }
        $collection = new PersistentCollection($this->dm, SimpleEntity::class, $firstHalf);
        $collection->add($secondHalf);

        // Act
        $retrievedIds = $collection->getIdList();

        // Assert
        $this->assertEquals($ids, $retrievedIds);
    }

    /**
     * @expectedException Exception
     */
    public function testSortByWhenPassingANonExistantProperty()
    {
        // Arrange
        $this->setUpSimpleEntity();
        $se = new SimpleEntity(1, 2, '1', '2');
        $this->dm->persist($se);
        $this->dm->flush();
        $collection = new PersistentCollection($this->dm, SimpleEntity::class);
        $collection->add($se);

        // Act
        $collection->sortBy('rubbish')->count();
    }

    public function testSortByWithUnloadedCollection()
    {
        // Arrange
        $this->setUpSimpleEntity();
        $sortedIds = [];
        $entities = [];
        for ($i = 0; $i < 100; $i++)
        {
            $entity = new SimpleEntity($i, 2, 'one', 'two');
            $entities[] = $entity;
            $this->dm->persist($entity);
        }
        $this->dm->flush();
        for ($i = 0; $i < 100; $i++)
        {
            $sortedIds[$entities[$i]->getId()] = null;
        }
        $collection = new PersistentCollection($this->dm, SimpleEntity::class, $sortedIds);
        $sortedIds = array_keys($sortedIds);

        // Act
        $collection->sortBy('one', false);
        $retrievedIds = $collection->getIdList();
        rsort($sortedIds);

        // Assert
        $this->assertEquals($sortedIds, $retrievedIds);

        // Act
        $collection->sortBy('one', true);
        $retrievedIds = $collection->getIdList();
        sort($sortedIds);

        // Assert
        $this->assertEquals($sortedIds, $retrievedIds);
    }

    public function testSortByWithHalfLoadedCollection()
    {
        // Arrange
        $this->setUpSimpleEntity();
        $sortedIds = [];
        $firstHalf = [];
        $secondHalf = [];
        $entities = [];
        for ($i = 0; $i < 100; $i++)
        {
            $entity = new SimpleEntity($i, 2, 'one', 'two');
            $entities[] = $entity;
            $this->dm->persist($entity);
        }
        $this->dm->flush();
        for ($i = 0; $i < 100; $i++)
        {
            $sortedIds[] = $entities[$i]->getId();
            if ($i < 50)
            {
                $firstHalf[$entities[$i]->getId()] = null;
                continue;
            }
            $secondHalf[] = $entities[$i];
        }
        $collection = new PersistentCollection($this->dm, SimpleEntity::class, $firstHalf);
        $collection->add($secondHalf);

        // Act
        $collection->sortBy('one', false);
        rsort($sortedIds);

        // Assert
        $this->assertEquals($sortedIds, $collection->getIdList());

        // Act
        $collection->sortBy('one', true);
        sort($sortedIds);

        // Assert
        $this->assertEquals($sortedIds, $collection->getIdList());
    }

    public function testWhere()
    {
        // Arrange
        $this->setUpSimpleEntity();
        $entities = [];
        for ($i = 0; $i < 50; $i++)
        {
            $entity = new SimpleEntity($i, 2, '1', '2');
            $entities[] = $entity;
            $this->dm->persist($entity);
        }
        $this->dm->flush();
        $ids = [];
        for ($i = 0; $i < 50; $i++)
        {
            $ids[$entities[$i]->getId()] = null;
        }
        $collection = new PersistentCollection($this->dm, SimpleEntity::class, $ids);

        // Act
        $first = $collection->where('one', '>', 25)->first();

        // Assert
        $this->assertEquals(26, $first->getOne());
    }

    public function testStateIsResetAfterSelecting()
    {
        // Arrange
        $this->setUpSimpleEntity();
        $entities = [];
        for ($i = 0; $i < 50; $i++)
        {
            $entity = new SimpleEntity($i, 2, '1', '2');
            $entities[] = $entity;
            $this->dm->persist($entity);
        }
        $this->dm->flush();
        $ids = [];
        for ($i = 0; $i < 50; $i++)
        {
            $ids[$entities[$i]->getId()] = null;
        }
        $collection = new PersistentCollection($this->dm, SimpleEntity::class, $ids);

        // Act
        $first = $collection->where('one', '>', 25)->first();
        $firstAgain = $collection->first();

        // Assert
        $this->assertEquals(26, $first->getOne());
        $this->assertEquals(0, $firstAgain->getOne());
    }
}