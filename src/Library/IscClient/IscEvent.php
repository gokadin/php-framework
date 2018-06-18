<?php

namespace Library\IscClient;

class IscEvent extends IscEntity
{
    public function __construct(string $message)
    {
        parent::__construct(IscEntity::ISC_EVENT, $message);
    }
}