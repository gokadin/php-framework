<?php

namespace Tests\App\Isc\Accounts;

use Library\IscClient\Controllers\IscController;
use Library\IscClient\IscEvent;

class AccountEvents extends IscController
{
    private $onAccountCreatedCalled = false;

    public function onAccountCreated(IscEvent $event)
    {
        $this->onAccountCreatedCalled = true;
    }

    public function accountCreatedCalled()
    {
        return $this->onAccountCreatedCalled;
    }
}