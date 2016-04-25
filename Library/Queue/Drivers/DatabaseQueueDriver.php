<?php

namespace Library\Queue\Drivers;

use App\Events\Event;
use Carbon\Carbon;
use Library\Facades\DB;

class DatabaseQueueDriver
{
    protected $queueTable;
    protected $failedTable;

    public function __construct($settings)
    {
        $this->queueTable = $settings['table'];
        $this->failedTable = $settings['failedTable'];

        $this->createTables();
    }

    public function push($job, $handler = null)
    {
        $serializedJob = serialize($job);
        $serializedHandler = 'undefined';
        $type = 'job';

        if ($job instanceof Event)
        {
            $type = 'eventListener';
            $serializedHandler = serialize($handler);
            $job = $handler;
        }

        $this->insertJobInDatabase($job, $serializedJob, $serializedHandler, $type);
    }

    protected function insertJobInDatabase($job, $data, $handler, $type)
    {
        $executionDate = Carbon::now();
        $executionDate->addSeconds($job->delay);

        DB::exec('INSERT INTO '.$this->queueTable.' (max_attempts, execution_date, job, handler, type) VALUES('.
            $job->maxAttempts.', '.
            '\''.$executionDate.'\', '.
            '\''.str_replace('\\', '\\\\', $data).'\', '.
            '\''.str_replace('\\', '\\\\', $handler).'\', '.
            '\''.$type.'\')');
    }

    protected function createTables()
    {
        DB::exec('CREATE TABLE IF NOT EXISTS '.$this->queueTable.' ('.
            'id INT(11) AUTO_INCREMENT PRIMARY KEY, '.
            'max_attempts INT(11) NOT NULL, '.
            'execution_date DATETIME NOT NULL, '.
            'job TEXT NOT NULL, '.
            'handler TEXT NOT NULL, '.
            'type VARCHAR(50) NOT NULL'.
            ')');

        DB::exec('CREATE TABLE IF NOT EXISTS '.$this->failedTable.' ('.
            'id INT(11) AUTO_INCREMENT PRIMARY KEY, '.
            'job TEXT NOT NULL, '.
            'handler TEXT NOT NULL)');
    }
}