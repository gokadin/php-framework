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
    public const RESULT_TYPE = 'RESULT';

    public const STATUS_OK = 200;
    public const STATUS_BAD_REQUEST = 400;
    public const STATUS_BAD_UNAUTHORIZED = 401;
    public const STATUS_NOT_FOUND = 404;
    public const STATUS_INTERNAL_SERVER_ERROR = 500;
}