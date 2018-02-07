<?php

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
}
set_error_handler("exception_error_handler");

date_default_timezone_set('America/Montreal');

require __DIR__.'/../Library/Configuration/envFunctions.php';

configureEnvironment();

switch (env('APP_DEBUG'))
{
    case 'true':
        error_reporting(E_ALL);
        break;
    default:
        error_reporting(0);
        break;
}

require __DIR__.'/../vendor/autoload.php';

session_write_close();