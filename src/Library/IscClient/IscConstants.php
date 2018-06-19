<?php

namespace Library\IscClient;

abstract class IscConstants
{
    public const EVENT_METHOD_PREFIX = 'on';
    public const COMMAND_METHOD_PREFIX = 'handle';
    public const QUERY_METHOD_PREFIX = 'handle';

    public const EVENT_TYPE = 'EVENT';
    public const COMMAND_TYPE = 'COMMAND';
    public const QUERY_TYPE = 'QUERY';
}