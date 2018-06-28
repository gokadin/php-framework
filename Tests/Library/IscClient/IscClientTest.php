<?php

namespace Tests\Library\IscClient;

use Library\IscClient\IscClient;

class IscClientTest extends IscClientBaseTest
{
    /**
     * @var IscClient
     */
    private $isc;

    public function setUp()
    {
        parent::setUp();

        $this->isc = new IscClient($this->basePath(), yaml_parse_file($this->basePath().'/Config/FeaturesConfig/isc.yml'));
    }

    /**
     * @runInSeparateProcess
     */
    public function test_some()
    {
        var_dump($this->predis->pubSub('numsub'));
        $this->assertTrue(true);
    }
}