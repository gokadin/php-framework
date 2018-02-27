<?php

namespace Tests\App\Http\Middlewares;

use Library\Http\Middleware;
use Library\Http\Response;

class MidTwo extends Middleware
{
    public function handle()
    {
        return new Response(Response::STATUS_UNAUTHORIZED);
    }
}