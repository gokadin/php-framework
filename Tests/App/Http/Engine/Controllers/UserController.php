<?php

namespace Tests\App\Http\Engine\Controllers;

use Library\Engine\EngineController;

class UserController extends EngineController
{
    /**
     * @var bool
     */
    private $fetchHookCalled = false;

    /**
     * @var bool
     */
    private $createHookCalled = false;

    /**
     * @var bool
     */
    private $updateHookCalled = false;

    /**
     * @var bool
     */
    private $deleteHookCalled = false;

    public function onFetch()
    {
        $this->fetchHookCalled = true;
    }

    public function onCreate()
    {
        $this->createHookCalled = true;
    }

    public function onUpdate()
    {
        $this->updateHookCalled = true;
    }

    public function onDelete()
    {
        $this->deleteHookCalled = true;
    }

    public function isFetchHookCalled(): bool
    {
        return $this->fetchHookCalled;
    }

    public function isCreateHookCalled(): bool
    {
        return $this->createHookCalled;
    }

    public function isUpdateHookCalled(): bool
    {
        return $this->updateHookCalled;
    }

    public function isDeleteHookCalled(): bool
    {
        return $this->deleteHookCalled;
    }
}