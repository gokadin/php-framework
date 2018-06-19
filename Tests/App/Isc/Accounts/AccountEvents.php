<?php

namespace Tests\App\Isc\Accounts;

use Library\IscClient\Controllers\IscController;

class AccountEvents extends IscController
{
    private $onAccountCreatedCalled = false;

    public function onAccountCreated(array $payload)
    {
        $this->onAccountCreatedCalled = true;
    }

    public function accountCreatedCalled()
    {
        return $this->onAccountCreatedCalled;
    }
}