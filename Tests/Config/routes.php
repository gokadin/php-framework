<?php

/**
 * Route builder tests
 */

$this->get('/simple-get', 'controllerA@actionA');
$this->post('/simple-post', 'controllerA@actionA');
$this->put('/simple-put', 'controllerA@actionA');
$this->patch('/simple-patch', 'controllerA@actionA');
$this->delete('/simple-delete', 'controllerA@actionA');
$this->many(['GET', 'DELETE'], '/simple-many', 'controllerA@actionA');

$this->get('/simple-middleware', 'controllerA@actionA', ['middleware' => 'm1']);
$this->get('/multiple-middlewares', 'controllerA@actionA', ['middleware' => ['m1', 'm2']]);
$this->get('/simple-name', 'controllerA@actionA', ['as' => 'name1']);

$this->group(['namespace' => 'NamespaceA', 'prefix' => 'prefixA', 'as' => 'groupA', 'middleware' => 'm1'], function()
{
    $this->get('/group-get', 'controllerA@actionA');
    $this->get('/group-get-with-middleware', 'controllerA@actionA', ['middleware' => 'm2']);
    $this->get('/group-get-with-name', 'controllerA@actionA', ['as' => 'name1']);

    $this->group(['namespace' => 'NamespaceB', 'prefix' => 'prefixB', 'as' => 'groupB', 'middleware' => ['m2', 'm3']], function()
    {
        $this->get('/multi-group-get', 'controllerA@actionA', ['as' => 'name1']);
        $this->get('/middleware-order', 'controllerA@actionA', ['middleware' => ['m4', 'm5'], 'as' => 'middlewareOrder']);
    });
});

$this->catchAll(['GET', 'POST'], 'controllerA@actionA', ['as' => 'catchAll', 'middleware' => 'm1']);