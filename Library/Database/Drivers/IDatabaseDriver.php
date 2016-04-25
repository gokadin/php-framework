<?php

namespace Library\Database\Drivers;

use Library\Database\Table;

interface IDatabaseDriver
{
    function table($table);

    function insert(array $data);

    function select(array $data);

    function update(array $data);

    function delete();

    function create(Table $table);

    function dropAll();

    function beginTransaction();

    function commit();

    function rollBack();
}