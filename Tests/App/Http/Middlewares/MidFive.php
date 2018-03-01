<?php

namespace Tests\App\Http\Middlewares;

use Library\Http\Middleware;
use Library\Http\Response;
use Tests\TestData\Router\ResolvableOne;

class MidFive extends Middleware
{
    public function handle(ResolvableOne $one)
    {
        if (!is_null($one) && $one instanceof ResolvableOne)
        {
            return true;
        }

        return new Response(Response::STATUS_BAD_REQUEST);
    }
}