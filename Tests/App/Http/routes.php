<?php

$this->get('/a', 'controllerA@actionA');
$this->get('/b', 'controllerA@actionA', ['middleware' => 'm1']);
$this->get('/c', 'controllerA@actionA', ['middleware' => ['m1', 'm2']]);
$this->get('/d', 'controllerA@actionA', ['as' => 'name1']);