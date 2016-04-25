<?php

namespace Tests\TestData\Queue;

use App\Jobs\Job;
use Tests\TestData\Container\ConcreteNoConstructor;

class ResolvableJob extends Job
{
    protected $wasRun;
    protected $handleDependency;

    public function __construct()
    {
        $this->wasRun = false;
        $this->handleDependency = null;
    }

    public function handle(ConcreteNoConstructor $handleDependency)
    {
        $this->wasRun = true;
        $this->handleDependency = $handleDependency;
    }

    public function wasRun()
    {
        return $this->wasRun;
    }

    public function handleDependency()
    {
        return $this->handleDependency;
    }
}