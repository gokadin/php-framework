<?php

namespace Tests\Library\IscClient;

use Tests\BaseTest;
use Predis\Client;

abstract class IscClientBaseTest extends BaseTest
{
    protected $predis;

    public function setUp()
    {
        parent::setUp();

        $this->loadEnvironment();

        $this->setUpRedis();
    }

    private function setUpRedis()
    {
        $this->predis = new Client([
            'scheme' => 'tcp',
            'host' => getenv('REDIS_HOST'),
            'port' => getenv('REDIS_PORT')
        ]);
    }
}