<?php

namespace Library\DataMapper;

interface Observable
{
    /**
     * Keeps track of the observer for notifications.
     *
     * @param Observer $observer
     * @return mixed
     */
    function subscribe(Observer $observer);
}