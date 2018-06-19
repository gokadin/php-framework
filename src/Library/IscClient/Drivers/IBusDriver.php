<?php

namespace Library\IscClient\Drivers;

interface IBusDriver
{
    public function subscribe(array $subscriptionStrings);

    public function run(\Closure $closure);

    public function stop();
}