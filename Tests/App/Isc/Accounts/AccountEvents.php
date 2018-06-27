<?php

namespace Tests\App\Isc\Accounts;

use Library\IscClient\Controllers\IscController;

class AccountEvents extends IscController
{
    private $onAccountCreatedCalled = false;

    public function onAccountCreated()
    {
        $this->onAccountCreatedCalled = true;
    }

    public function accountCreatedCalled()
    {
        return $this->onAccountCreatedCalled;
    }
}