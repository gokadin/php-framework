<?php

namespace Library\Database;

class Schema
{
    protected $tables = [];
    protected $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function add(Table $table)
    {
        $this->tables[$table->name()] = $table;
    }

    public function tables()
    {
        return $this->tables;
    }

    public function table($tableName)
    {
        return isset($this->tables[$tableName]) ? $this->tables[$tableName] : null;
    }

    public function create($tableName)
    {
        if (!isset($this->tables[$tableName]))
        {
            return;
        }

        $this->database->create($this->tables[$tableName]);
    }

    public function createAll()
    {
        foreach ($this->tables as $table)
        {
            $this->database->create($this->tables[$table->name()]);
        }
    }

    public function drop($tableName)
    {
        if (!isset($this->tables[$tableName]))
        {
            return;
        }

        $this->database->drop($tableName);
    }

    public function dropAll()
    {
        foreach ($this->tables as $table)
        {
            $this->database->drop($table->name());
        }
    }
}