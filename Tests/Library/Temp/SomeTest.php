<?php

namespace Tests\Library\Temp;

use Tests\BaseTest;

class SomeTest extends BaseTest
{
    private $orm;

    public function setUp()
    {
        parent::setUp();

        $this->orm = [
            'n1'
        ];
    }

    public function test_read()
    {
        file_put_contents('x', 'lala');

        $startTime = microtime(true);

        $x = file_get_contents('x');

        $endTime = microtime(true);
        $time = $endTime - $startTime;
        echo ($time * 1000).' ms';

        unlink('x');

        $this->assertTrue(true);
    }

    public function test_write()
    {
        $startTime = microtime(true);

        file_put_contents('y', 'lala');

        $endTime = microtime(true);
        $time = $endTime - $startTime;
        echo ($time * 1000).' ms';

        unlink('y');

        $this->assertTrue(true);
    }

    public function test_select()
    {
        $command = 'select * from user';
        $commandHash = 'a';

        $this->assertTrue(true);
    }
}