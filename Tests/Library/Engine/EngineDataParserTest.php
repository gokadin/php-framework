<?php

namespace Tests\Library\Engine;

use Library\Http\Response;
use Tests\App\Models\Comment;
use Tests\App\Models\Post;
use Tests\App\Models\User;

class EngineDataParserTest extends EngineBaseTest
{
    public function test_processData_fetchSimplest()
    {
        // Arrange
        $this->setUpEngineWithUser();
        $this->dm->persist(new User('one', 1));
        $this->dm->flush();

        // Act
        $result = $this->engine->processData([
            'fetch' => [
                'User' => [
                    'fields' => ['id']
                ]
            ]
        ]);

        // Assert
        $this->assertEquals(Response::STATUS_OK, $result['status']);
        $this->assertEquals(['User' => [
            ['id' => 1]
        ]], $result['content']);
    }

    public function test_processData_fetchWithAs()
    {
        // Arrange
        $this->setUpEngineWithUser();
        $this->dm->persist(new User('one', 1));
        $this->dm->flush();

        // Act
        $result = $this->engine->processData([
            'fetch' => [
                'User' => [
                    'fields' => [
                        'id' => ['as' => 'some']
                    ]
                ]
            ]
        ]);

        // Assert
        $this->assertEquals(Response::STATUS_OK, $result['status']);
        $this->assertEquals(['User' => [
            ['some' => 1]
        ]], $result['content']);
    }

    public function test_processData_fetchWithConditions()
    {
        // Arrange
        $this->setUpEngineWithUser();
        $this->dm->persist(new User('one', 1));
        $this->dm->persist(new User('two', 2));
        $this->dm->persist(new User('three', 3));
        $this->dm->persist(new User('four', 4));
        $this->dm->persist(new User('five', 5));
        $this->dm->persist(new User('six', 6));
        $this->dm->flush();

        // Act
        $result = $this->engine->processData([
            'fetch' => [
                'User' => [
                    'fields' => [
                        'id' => ['as' => 'id']
                    ],
                    'conditions' => [
                        ['where', 'age', '<', 5],
                        ['orWhere', 'age', '=', 6],
                        ['sort', 'age', 'desc'],
                        ['limit', 3]
                    ]
                ]
            ]
        ]);

        // Assert
        $this->assertEquals(Response::STATUS_OK, $result['status']);
        $this->assertEquals(['User' => [
            ['id' => 6], ['id' => 4], ['id' => 3]
        ]], $result['content']);
    }

    public function test_processData_fetchHasMany()
    {
        // Arrange
        $this->setUpEngineWithPostsComments();
        $post = new Post('title');
        $this->dm->persist($post);
        $this->dm->persist(new Comment('text', $post));
        $this->dm->flush();

        // Act
        $result = $this->engine->processData([
            'fetch' => [
                'post' => [
                    'fields' => [
                        'title',
                        'comments' => [
                            'id',
                            'text'
                        ]
                    ]
                ]
            ]
        ]);

        // Assert
        $this->assertEquals(Response::STATUS_OK, $result['status']);
        $this->assertEquals([
            'post' => [
                [
                    'title' => 'title',
                    'comments' => [
                        [
                            'id' => 1,
                            'text' => 'text'
                        ]
                    ]
                ]
            ]], $result['content']);
    }

    public function test_processData_createSingle()
    {
        // Arrange
        $this->setUpEngineWithUser();

        // Act
        $result = $this->engine->processData([
            'create' => [
                'User' => [
                    'values' => [
                        'name' => 'one',
                        'age' => 1
                    ]
                ]
            ]
        ]);

        // Assert
        $this->assertEquals(Response::STATUS_OK, $result['status']);
        $this->assertEquals(['User' => []], $result['content']);
    }

    public function test_processData_createMultiple()
    {
        // Arrange
        $this->setUpEngineWithUser();

        // Act
        $result = $this->engine->processData([
            'create' => [
                'User' => [
                    'values' => [
                        [
                            'name' => 'one',
                            'age' => 1
                        ],
                        [
                            'name' => 'two',
                            'age' => 2
                        ]
                    ]
                ]
            ]
        ]);

        // Assert
        $this->assertEquals(Response::STATUS_OK, $result['status']);
        $this->assertEquals(['User' => []], $result['content']);
    }

    public function test_processData_createSingleWithFields()
    {
        // Arrange
        $this->setUpEngineWithUser();

        // Act
        $result = $this->engine->processData([
            'create' => [
                'User' => [
                    'values' => [
                        'name' => 'one',
                        'age' => 1
                    ],
                    'fields' => [
                        'id' => ['as' => 'id']
                    ]
                ]
            ]
        ]);

        // Assert
        $this->assertEquals(Response::STATUS_OK, $result['status']);
        $this->assertEquals(['User' => [
            ['id' => 1]
        ]], $result['content']);
    }

    public function test_processData_createMultipleWithFields()
    {
        // Arrange
        $this->setUpEngineWithUser();

        // Act
        $result = $this->engine->processData([
            'create' => [
                'User' => [
                    'values' => [
                        [
                            'name' => 'one',
                            'age' => 1
                        ],
                        [
                            'name' => 'two',
                            'age' => 2
                        ]
                    ],
                    'fields' => [
                        'id' => ['as' => 'id']
                    ]
                ]
            ]
        ]);

        // Assert
        $this->assertEquals(Response::STATUS_OK, $result['status']);
        $this->assertEquals(['User' => [
            ['id' => 1], ['id' => 2]
        ]], $result['content']);
    }

    public function test_processData_updateSimplestWithFields()
    {
        // Arrange
        $this->setUpEngineWithUser();
        $this->dm->persist(new User('one', 1));
        $this->dm->persist(new User('two', 2));
        $this->dm->flush();

        // Act
        $result = $this->engine->processData([
            'update' => [
                'User' => [
                    'values' => [
                        'name' => 'updatedName'
                    ],
                    'fields' => [
                        'id' => ['as' => 'id'],
                        'name' => ['as' => 'name'],
                        'age' => ['as' => 'age']
                    ]
                ]
            ]
        ]);

        // Assert
        $this->assertEquals(Response::STATUS_OK, $result['status']);
        $this->assertEquals(['User' => [
            ['id' => 1, 'name' => 'updatedName', 'age' => 1],
            ['id' => 2, 'name' => 'updatedName', 'age' => 2]
        ]], $result['content']);
    }

    public function test_processData_updateWithConditionsAndWithFields()
    {
        // Arrange
        $this->setUpEngineWithUser();
        $this->dm->persist(new User('one', 1));
        $this->dm->persist(new User('two', 2));
        $this->dm->flush();

        // Act
        $result = $this->engine->processData([
            'update' => [
                'User' => [
                    'values' => [
                        'name' => 'updatedName'
                    ],
                    'fields' => [
                        'id' => ['as' => 'id'],
                        'name' => ['as' => 'name'],
                        'age' => ['as' => 'age']
                    ],
                    'conditions' => [
                        ['where', 'id', '=', 2]
                    ]
                ]
            ]
        ]);

        // Assert
        $this->assertEquals(Response::STATUS_OK, $result['status']);
        $this->assertEquals(['User' => [
            ['id' => 2, 'name' => 'updatedName', 'age' => 2]
        ]], $result['content']);
    }

    public function test_processData_deleteSimplest()
    {
        // Arrange
        $this->setUpEngineWithUser();
        $this->dm->persist(new User('one', 1));
        $this->dm->persist(new User('two', 2));
        $this->dm->flush();

        // Act
        $result = $this->engine->processData([
            'delete' => [
                'User' => []
            ]
        ]);

        // Assert
        $this->assertEquals(Response::STATUS_OK, $result['status']);
        $users = $this->dm->findAll(User::class);
        $this->assertEquals(0, $users->count());
    }

    public function test_processData_deleteWithConditions()
    {
        // Arrange
        $this->setUpEngineWithUser();
        $this->dm->persist(new User('one', 1));
        $this->dm->persist(new User('two', 2));
        $this->dm->flush();

        // Act
        $result = $this->engine->processData([
            'delete' => [
                'User' => [
                    'conditions' => [
                        ['where', 'age', '=', 2]
                    ]
                ]
            ]
        ]);

        // Assert
        $this->assertEquals(Response::STATUS_OK, $result['status']);
        $users = $this->dm->findAll(User::class);
        $this->assertEquals(1, $users->count());
        $this->assertEquals(1, $users->first()->getAge());
    }
}