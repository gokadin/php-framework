<?php

namespace Library\Queue;

trait Queueable
{
    private $delay = 0;
    private $maxAttempts = 3;

    protected function setDelay($seconds)
    {
        $this->delay = $seconds;
    }

    public function getDelay()
    {
        return $this->delay;
    }

    protected function setMaxAttempts($number)
    {
        $this->maxAttempts = $number;
    }

    public function getMaxAttempts()
    {
        return $this->maxAttempts;
    }
}