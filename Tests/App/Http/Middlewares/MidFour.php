<?php

namespace Tests\App\Http\Middlewares;

use Library\Http\Middleware;
use Library\Http\Response;
use Tests\TestData\Router\ResolvableOne;

class MidFour extends Middleware
{
    /**
     * @var ResolvableOne
     */
    private $one;

    public function __construct(ResolvableOne $one)
    {
        $this->one = $one;
    }

    public function handle()
    {
        if (!is_null($this->one) && $this->one instanceof ResolvableOne)
        {
            return true;
        }

        return new Response(Response::STATUS_BAD_REQUEST);
    }
}