<?php

namespace Tests\App\Http\Middlewares\OrderTests;

use Library\Http\Middleware;
use Library\Http\Response;

class MidOrderOne extends Middleware
{
    public function handle(MidOrderResolvable $resolvable)
    {
        if ($resolvable->isCalled())
        {
            return new Response(Response::STATUS_BAD_REQUEST);
        }

        $resolvable->call();

        return true;
    }
}