<?php

namespace Library\IscClient\RequestHandlers;

class EntityHandler
{
    public function handle($request)
    {
        $strout = fopen('php://stdout', 'w');

        fwrite($strout, 'NEW REQUEST: '.var_dump($request).PHP_EOL);

        var_dump($request);
    }
}