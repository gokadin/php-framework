<?php

namespace Tests\Library\Engine;

use Library\Container\Container;
use Library\Engine\Engine;
use Tests\App\Models\Comment;
use Tests\App\Models\Post;
use Tests\App\Models\User;
use Tests\Library\DataMapper\DataMapperBaseTest;

abstract class EngineBaseTest extends DataMapperBaseTest
{
    /**
     * @var Engine
     */
    protected $engine;

    /**
     * @var Container
     */
    protected $container;

    private function setUpEngine(array $schema)
    {
        $this->container = new Container();
        $this->engine = new Engine($schema, $this->dm, $this->container, [
            'uri' => '/engine',
            'modelsPath' => 'Tests/App/Models',
            'controllersPath' => 'Tests/App/Http/Engine/Controllers'
        ]);
    }

    protected function setUpEngineWithUser()
    {
        $this->classes = [
            User::class
        ];

        $schema = [
            'user' => [
                'id' => [
                    'type' => 'string'
                ]
            ]
        ];

        $this->setUpBase();
        $this->setUpEngine($schema);
    }

    protected function setUpEngineWithPostsComments()
    {
        $this->classes = [
            Post::class,
            Comment::class
        ];

        $schema = [
            'post' => [
                'title' => ['type' => 'string'],
                'comments' => ['hasMany' => 'comment']
            ],
            'comment' => [
                'text' => ['type' => 'string'],
                'post' => ['belongsTo' => 'post']
            ]
        ];

        $this->setUpBase();
        $this->setUpEngine($schema);
    }
}