<?php

namespace Library\IscClient\Drivers;

interface IBusDriver
{
    public function dispatch(string $channel, array $payload);

    public function run(array $subscriptions, \Closure $closure);

    public function publishAndListenResult(string $dispatchChannel, array $dispatchPayload);
}