<?php

namespace Tests\App\Http\Engine\Controllers;

use Library\Engine\EngineController;

class UserController extends EngineController
{
    /**
     * @var bool
     */
    private $preFetchCalled = false;

    /**
     * @var bool
     */
    private $postFetchCalled = false;

    public function preFetch()
    {
        $this->preFetchCalled = true;
    }

    public function postFetch()
    {
        $this->postFetchCalled = true;
    }

    public function isPreFetchCalled(): bool
    {
        return $this->preFetchCalled;
    }

    public function isPostFetchCalled(): bool
    {
        return $this->postFetchCalled;
    }
}