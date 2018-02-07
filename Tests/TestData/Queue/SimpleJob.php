<?php

namespace Tests\TestData\Queue;

use App\Jobs\Job;

class SimpleJob extends Job
{
    protected $wasRun;

    public function __construct()
    {
        $this->wasRun = false;
    }

    public function handle()
    {
        $this->wasRun = true;
    }

    public function wasRun()
    {
        return $this->wasRun;
    }
}