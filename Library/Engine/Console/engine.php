<?php

require __DIR__.'/../../../Bootstrap/autoload.php';

use Symfony\Component\Console\Application;
use Library\Engine\Console\Modules\SynchronizeSchema;

$app = new Application();

$config = json_decode(file_get_contents(__DIR__.'/../../../Config/Datamapper/schema.json'), true);
$app->add(new SynchronizeSchema($config));

$app->run();
