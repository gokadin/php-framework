<?php

namespace Tests\App\Http\Middlewares\OrderTests;


class MidOrderResolvable
{
    /**
     * @var bool
     */
    private $isCalled = false;

    /**
     * Marks the object as accessed.
     */
    public function call(): void
    {
        $this->isCalled = true;
    }

    /**
     * @return bool
     */
    public function isCalled(): bool
    {
        return $this->isCalled;
    }
}