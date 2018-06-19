<?php

namespace Library\IscClient\Drivers;

interface IBusDriver
{
    public function subscribe(array $subscriptionStrings);

    public function dispatch(string $channel, array $payload);

    public function run(\Closure $closure);

    public function stop();
}