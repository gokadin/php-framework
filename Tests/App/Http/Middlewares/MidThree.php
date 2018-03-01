<?php

namespace Tests\App\Http\Middlewares;

use Library\Http\Middleware;
use Library\Http\Request;
use Library\Http\Response;

class MidThree extends Middleware
{
    public function handle()
    {
        if (!is_null($this->request) && $this->request instanceof Request)
        {
            return true;
        }

        return new Response(Response::STATUS_BAD_REQUEST);
    }
}