<?php

namespace Library\DataMapper;

interface Observer
{
    /**
     * Receives update notifications from the observable object.
     *
     * @param $event
     * @return mixed
     */
    function update($event);
}