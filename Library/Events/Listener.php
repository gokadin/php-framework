<?php

namespace Library\Events;

use Library\Queue\Queueable;

abstract class Listener implements Handler
{
    use Queueable;
}