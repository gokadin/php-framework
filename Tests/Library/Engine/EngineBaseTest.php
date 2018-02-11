<?php

namespace Tests\Library\Engine;

use Library\Container\Container;
use Library\Engine\Engine;
use Tests\App\Models\User;
use Tests\Library\DataMapper\DataMapperBaseTest;

abstract class EngineBaseTest extends DataMapperBaseTest
{
    /**
     * @var Engine
     */
    protected $engine;

    private function setUpEngine(array $schema)
    {
        $this->engine = new Engine($this->basePath(), $schema, $this->dm, new Container(), [
            'modelsPath' => 'Tests/App/Models'
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
}