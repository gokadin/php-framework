<?php

namespace Tests\App\Http\Middlewares;

use Library\Http\Middleware;

class MidOne extends Middleware
{
    public function handle()
    {
        return true;
    }
}