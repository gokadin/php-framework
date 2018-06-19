<?php

namespace Tests\App\Isc\Accounts;

use Library\IscClient\Controllers\IscEventController;
use Library\IscClient\IscEvent;

class AccountEvents extends IscEventController
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