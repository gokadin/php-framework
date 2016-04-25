<?php

namespace Tests\Log;

use Tests\BaseTest;
use Library\Log\Log;

class LogTest extends BaseTest
{
    /**
     * @var Log
     */
    private $log;

    public function setUp()
    {
        parent::setUp();

        date_default_timezone_set('America/Montreal');
    }

    private function setUpLog()
    {
        $this->log = new Log('../TestData/Logs');
    }

    public function testSetLogFolder()
    {
        // Arrange
        $this->setUpLog();

        // Act
        $this->log->setLogFolder('TestData/Logs');

        // Assert
        $this->assertEquals('TestData/Logs', $this->log->getLogFolder());
    }

    public function testSetLogFolderWithEndingSlash()
    {
        // Arrange
        $this->setUpLog();

        // Act
        $this->log->setLogFolder('TestData/Logs/');

        // Assert
        $this->assertEquals('TestData/Logs', $this->log->getLogFolder());
    }

    public function testLogFileNameIsCorrectlyGenerated()
    {
        // Arrange
        $this->setUpLog();
        $expectedFileName = '../TestData/Logs/log-'.date('d-m-Y');

        // Act
        $this->log->info('testing');

        // Assert
        $this->assertTrue(file_exists($expectedFileName));

        // Act
        unlink($expectedFileName);
    }
}