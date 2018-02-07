<?php

require __DIR__.'/../../Bootstrap/autoload.php';

require __DIR__.'/helper_functions.php';

use Symfony\Component\Console\Application;

$app = new Application();

$app->add(new \Library\Pyramid\Modules\GenPage());
$app->add(new \Library\Pyramid\Modules\GenComponent());
$app->add(new \Library\Pyramid\Modules\GenReducer());
$app->add(new \Library\Pyramid\Modules\GenAction());
$app->add(new \Library\Pyramid\Modules\Design());

$app->run();
