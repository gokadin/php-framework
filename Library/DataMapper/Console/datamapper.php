<?php

require __DIR__.'/../../../Bootstrap/autoload.php';

use Symfony\Component\Console\Application;
use Library\DataMapper\Console\Modules\CreateSchema;
use Library\DataMapper\Console\Modules\DropSchema;
use Library\DataMapper\Console\Modules\UpdateSchema;
use Library\DataMapper\Console\Modules\SeedDatabase;

$app = new Application();

$config = require __DIR__.'/../../../Config/Datamapper/datamapper.php';
$app->add(new CreateSchema($config));
$app->add(new DropSchema($config));
$app->add(new UpdateSchema($config));
$app->add(new SeedDatabase($config));

$app->run();
