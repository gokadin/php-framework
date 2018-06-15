<?php

namespace Library\IscClient\Drivers;

interface IBusDriver
{
    public function subscribe();

    public function run();

    public function stop();
}