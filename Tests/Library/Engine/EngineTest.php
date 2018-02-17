<?php

namespace Tests\Library\Engine;

use Library\Http\Response;
use Tests\App\Http\Engine\Controllers\UserController;
use Tests\App\Models\User;

class EngineTest extends EngineBaseTest
{
    public function test_run_fetchSimplestWithoutData()
    {
        // Arrange
        $this->setUpEngineWithUser();

        // Act
        $this->engine->fetch('User', ['id' => ['as' => 'id']]);
        $result = $this->engine->run();

        // Assert
        $this->assertEquals(Response::STATUS_OK, $result['status']);
        $this->assertEquals(['User' => []], $result['content']);
    }

    public function test_run_fetchSimplestWithOneEntry()
    {
        // Arrange
        $this->setUpEngineWithUser();
        $this->dm->persist(new User('one', 1));
        $this->dm->flush();

        // Act
        $this->engine->fetch('User', ['id' => ['as' => 'id']]);
        $result = $this->engine->run();

        // Assert
        $this->assertEquals(Response::STATUS_OK, $result['status']);
        $this->assertEquals(['User' => [
            ['id' => 1]
        ]], $result['content']);
    }

    public function test_run_fetchSimplestWithTwoEntries()
    {
        // Arrange
        $this->setUpEngineWithUser();
        $this->dm->persist(new User('one', 1));
        $this->dm->persist(new User('two', 2));
        $this->dm->flush();

        // Act
        $this->engine->fetch('User', ['id' => ['as' => 'id']]);
        $result = $this->engine->run();

        // Assert
        $this->assertEquals(Response::STATUS_OK, $result['status']);
        $this->assertEquals(['User' => [
            ['id' => 1], ['id' => 2]
        ]], $result['content']);
    }

    public function test_run_fetchSimplestWithTwoEntriesAndMultipleFields()
    {
        // Arrange
        $this->setUpEngineWithUser();
        $this->dm->persist(new User('one', 1));
        $this->dm->persist(new User('two', 2));
        $this->dm->flush();

        // Act
        $this->engine->fetch('User', [
            'id' => ['as' => 'id'],
            'name' => ['as' => 'name'],
            'age' => ['as' => 'age']
        ]);
        $result = $this->engine->run();

        // Assert
        $this->assertEquals(Response::STATUS_OK, $result['status']);
        $this->assertEquals(['User' => [
            [
                'id' => 1,
                'name' => 'one',
                'age' => 1
            ],
            [
                'id' => 2,
                'name' => 'two',
                'age' => 2
            ]
        ]], $result['content']);
    }

    public function test_run_fetchSimplestWithAsBeingDifferent()
    {
        // Arrange
        $this->setUpEngineWithUser();
        $this->dm->persist(new User('one', 1));
        $this->dm->flush();

        // Act
        $this->engine->fetch('User', ['id' => ['as' => 'something']]);
        $result = $this->engine->run();

        // Assert
        $this->assertEquals(Response::STATUS_OK, $result['status']);
        $this->assertEquals(['User' => [
            ['something' => 1]
        ]], $result['content']);
    }

    public function test_run_fetchWithSimpleWhereCondition()
    {
        // Arrange
        $this->setUpEngineWithUser();
        $this->dm->persist(new User('one', 1));
        $this->dm->persist(new User('two', 2));
        $this->dm->persist(new User('three', 3));
        $this->dm->flush();

        // Act
        $this->engine->fetch('User', ['id' => ['as' => 'id']])
            ->where('name', '=', 'two');
        $result = $this->engine->run();

        // Assert
        $this->assertEquals(Response::STATUS_OK, $result['status']);
        $this->assertEquals(['User' => [
            ['id' => 2]
        ]], $result['content']);
    }

    public function test_run_fetchSimpleWithMultipleWhereCondition()
    {
        // Arrange
        $this->setUpEngineWithUser();
        $this->dm->persist(new User('one', 1));
        $this->dm->persist(new User('one', 2));
        $this->dm->persist(new User('one', 3));
        $this->dm->flush();

        // Act
        $this->engine->fetch('User', ['id' => ['as' => 'id']])
            ->where('name', '=', 'one')
            ->where('age', '>', 1);
        $result = $this->engine->run();

        // Assert
        $this->assertEquals(Response::STATUS_OK, $result['status']);
        $this->assertEquals(['User' => [
            ['id' => 2], ['id' => 3]
        ]], $result['content']);
    }

    public function test_run_fetchSimpleWithOrWhereCondition()
    {
        // Arrange
        $this->setUpEngineWithUser();
        $this->dm->persist(new User('one', 1));
        $this->dm->persist(new User('one', 2));
        $this->dm->persist(new User('one', 3));
        $this->dm->flush();

        // Act
        $this->engine->fetch('User', ['id' => ['as' => 'id']])
            ->where('id', '=', 1)
            ->orWhere('age', '=', 3);
        $result = $this->engine->run();

        // Assert
        $this->assertEquals(Response::STATUS_OK, $result['status']);
        $this->assertEquals(['User' => [
            ['id' => 1], ['id' => 3]
        ]], $result['content']);
    }

    public function test_run_fetchSimpleWithInvalidOrWhereCondition()
    {
        // Arrange
        $this->setUpEngineWithUser();

        // Act
        $this->engine->fetch('User', ['id' => ['as' => 'id']])
            ->where('id', '=', 1)
            ->orWhere('rubbish', '=', 3);
        $result = $this->engine->run();

        // Assert
        $this->assertEquals(Response::STATUS_BAD_REQUEST, $result['status']);
    }

    public function test_run_fetchWithSorting()
    {
        // Arrange
        $this->setUpEngineWithUser();
        $this->dm->persist(new User('one', 1));
        $this->dm->persist(new User('two', 2));
        $this->dm->persist(new User('three', 3));
        $this->dm->flush();

        // Act
        $this->engine->fetch('User', ['id' => ['as' => 'id']])
            ->sort('id', false);
        $result = $this->engine->run();

        // Assert
        $this->assertEquals(Response::STATUS_OK, $result['status']);
        $this->assertEquals(['User' => [
            ['id' => 3], ['id' => 2], ['id' => 1]
        ]], $result['content']);
    }

    public function test_run_fetchWithSortingWithStrings()
    {
        // Arrange
        $this->setUpEngineWithUser();
        $this->dm->persist(new User('b', 1));
        $this->dm->persist(new User('a', 2));
        $this->dm->persist(new User('c', 3));
        $this->dm->flush();

        // Act
        $this->engine->fetch('User', ['id' => ['as' => 'id']])
            ->sort('name');
        $result = $this->engine->run();

        // Assert
        $this->assertEquals(Response::STATUS_OK, $result['status']);
        $this->assertEquals(['User' => [
            ['id' => 2], ['id' => 1], ['id' => 3]
        ]], $result['content']);
    }

    public function test_run_fetchWithLimit()
    {
        // Arrange
        $this->setUpEngineWithUser();
        $this->dm->persist(new User('one', 1));
        $this->dm->persist(new User('two', 2));
        $this->dm->persist(new User('three', 3));
        $this->dm->flush();

        // Act
        $this->engine->fetch('User', ['id' => ['as' => 'id']])
            ->limit(2);
        $result = $this->engine->run();

        // Assert
        $this->assertEquals(Response::STATUS_OK, $result['status']);
        $this->assertEquals(['User' => [
            ['id' => 1], ['id' => 2]
        ]], $result['content']);
    }

    public function test_run_fetchCallsHook()
    {
        // Arrange
        $this->setUpEngineWithUser();

        // Act
        $this->engine->fetch('User', ['id' => ['as' => 'id']]);
        $result = $this->engine->run();

        // Assert
        $controller = $this->container->resolve(UserController::class);
        $this->assertTrue($controller->isFetchHookCalled());
        $this->assertEquals(Response::STATUS_OK, $result['status']);
    }

    /**
     * CREATE
     */

    public function test_run_createSimplest()
    {
        // Arrange
        $this->setUpEngineWithUser();

        // Act
        $this->engine->create('User', [
            'name' => 'one',
            'age' => 1
        ]);
        $result = $this->engine->run();

        // Assert
        $this->assertEquals(Response::STATUS_OK, $result['status']);
        $users = $this->dm->findAll(User::class);
        $this->assertEquals(1, $users->count());
        $user = $users->first();
        $this->assertEquals('one', $user->getName());
        $this->assertEquals(1, $user->getAge());
    }

    public function test_run_createWithFetch()
    {
        // Arrange
        $this->setUpEngineWithUser();

        // Act
        $this->engine->create('User', [
            'name' => 'one',
            'age' => 1
        ], ['id' => ['as' => 'id']]);
        $result = $this->engine->run();

        // Assert
        $this->assertEquals(Response::STATUS_OK, $result['status']);
        $this->assertEquals(['User' => [
            ['id' => 1]
        ]], $result['content']);
    }

    public function test_run_createMultiple()
    {
        // Arrange
        $this->setUpEngineWithUser();

        // Act
        $this->engine->create('User', [
            [
                'name' => 'one',
                'age' => 1
            ],
            [
                'name' => 'two',
                'age' => 2
            ]
        ], [
            'id' => ['as' => 'id'],
            'name' => ['as' => 'name']
        ]);
        $result = $this->engine->run();

        // Assert
        $this->assertEquals(Response::STATUS_OK, $result['status']);
        $this->assertEquals(['User' => [
            ['id' => 1, 'name' => 'one'], ['id' => 2, 'name' => 'two']
        ]], $result['content']);
    }

    public function test_run_createCallsHook()
    {
        // Arrange
        $this->setUpEngineWithUser();

        // Act
        $this->engine->create('User', [
            'name' => 'one',
            'age' => 1
        ]);
        $result = $this->engine->run();

        // Assert
        $controller = $this->container->resolve(UserController::class);
        $this->assertTrue($controller->isCreateHookCalled());
        $this->assertEquals(Response::STATUS_OK, $result['status']);
    }

    /**
     * DELETE
     */

    public function test_run_deleteAll()
    {
        // Arrange
        $this->setUpEngineWithUser();
        $this->dm->persist(new User('one', 1));
        $this->dm->persist(new User('two', 2));
        $this->dm->persist(new User('three', 3));
        $this->dm->flush();

        // Act
        $this->engine->delete('User');
        $result = $this->engine->run();

        // Assert
        $this->assertEquals(Response::STATUS_OK, $result['status']);
        $this->assertEquals(0, $this->dm->findAll(User::class)->count());
    }

    public function test_run_deleteWhere()
    {
        // Arrange
        $this->setUpEngineWithUser();
        $this->dm->persist(new User('one', 1));
        $this->dm->persist(new User('two', 2));
        $this->dm->persist(new User('three', 3));
        $this->dm->flush();

        // Act
        $this->engine->delete('User')->where('age', '>', 1);
        $result = $this->engine->run();

        // Assert
        $this->assertEquals(Response::STATUS_OK, $result['status']);
        $this->assertEquals(1, $this->dm->findAll(User::class)->count());
    }

    public function test_run_deleteCallsHook()
    {
        // Arrange
        $this->setUpEngineWithUser();

        // Act
        $this->engine->delete('User');
        $result = $this->engine->run();

        // Assert
        $controller = $this->container->resolve(UserController::class);
        $this->assertTrue($controller->isDeleteHookCalled());
        $this->assertEquals(Response::STATUS_OK, $result['status']);
    }

    /**
     * UPDATE
     */

    public function test_run_updateAll()
    {
        // Arrange
        $this->setUpEngineWithUser();
        $this->dm->persist(new User('one', 1));
        $this->dm->persist(new User('two', 2));
        $this->dm->flush();

        // Act
        $this->engine->update('User', [
            'name' => 'updatedName'
        ]);
        $result = $this->engine->run();

        // Assert
        $this->assertEquals(Response::STATUS_OK, $result['status']);
        $users = $this->dm->findAll(User::class);
        $this->assertEquals(2, $users->count());
        $user1 = $users->first();
        $user2 = $users->last();
        $this->assertEquals('updatedName', $user1->getName());
        $this->assertEquals(1, $user1->getAge());
        $this->assertEquals('updatedName', $user2->getName());
        $this->assertEquals(2, $user2->getAge());
    }

    public function test_run_updateAllWithFields()
    {
        // Arrange
        $this->setUpEngineWithUser();
        $this->dm->persist(new User('one', 1));
        $this->dm->persist(new User('two', 2));
        $this->dm->flush();

        // Act
        $this->engine->update('User', [
            'name' => 'updatedName'
        ], [
            'name' => ['as' => 'name'],
            'age' => ['as' => 'age']
        ]);
        $result = $this->engine->run();

        // Assert
        $this->assertEquals(Response::STATUS_OK, $result['status']);
        $this->assertEquals(['User' => [
            ['name' => 'updatedName', 'age' => 1], ['name' => 'updatedName', 'age' => 2]
        ]], $result['content']);
    }

    public function test_run_updateWhere()
    {
        // Arrange
        $this->setUpEngineWithUser();
        $this->dm->persist(new User('one', 1));
        $this->dm->persist(new User('two', 2));
        $this->dm->flush();

        // Act
        $this->engine->update('User', [
            'name' => 'updatedName'
        ], [
            'name' => ['as' => 'name'],
            'age' => ['as' => 'age']
        ])->where('id', '=', 2);
        $result = $this->engine->run();

        // Assert
        $this->assertEquals(Response::STATUS_OK, $result['status']);
        $this->assertEquals(['User' => [
            ['name' => 'updatedName', 'age' => 2]
        ]], $result['content']);
    }

    public function test_run_updateCallsHook()
    {
        // Arrange
        $this->setUpEngineWithUser();

        // Act
        $this->engine->update('User', [
            'name' => 'updatedName'
        ]);
        $result = $this->engine->run();

        // Assert
        $controller = $this->container->resolve(UserController::class);
        $this->assertTrue($controller->isUpdateHookCalled());
        $this->assertEquals(Response::STATUS_OK, $result['status']);
    }

    /**
     * HOOKS
     */

    // ...
}