<?php

namespace Library\Engine\Events;

abstract class EngineEvent
{
    /**
     * @var mixed
     */
    private $entity;

    public function __construct($entity)
    {
        $this->entity = $entity;
    }

    public function entity()
    {
        return $this->entity;
    }
}