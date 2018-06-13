<?php

namespace Tests\App\Http\Engine\Validations;

use Library\Validation\ValidationBase;

class UserValidation extends ValidationBase
{
    /**
     * @var bool
     */
    private $wasOnFetchCalled = false;

    public function onFetch()
    {
        $this->wasOnFetchCalled = true;

        return true;
    }

    public function wasOnFetchCalled()
    {
        return $this->wasOnFetchCalled;
    }
}