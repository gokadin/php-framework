<?php

namespace Tests\Library\Engine;

use Library\Container\Container;
use Library\Engine\Engine;
use Tests\Library\DataMapper\DataMapperBaseTest;
use Tests\TestData\Engine\User;

class EngineBaseTest extends DataMapperBaseTest
{
    /**
     * @var Engine
     */
    protected $engine;

    private function setUpEngine(array $schema)
    {
        $this->engine = new Engine($schema, $this->dm, new Container());
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