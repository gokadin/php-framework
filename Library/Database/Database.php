<?php

namespace Library\Database;

use Library\Database\Drivers\PdoDatabaseDriver;
use Library\Database\Drivers\RedisDatabaseDriver;
use Library\Config;
use Symfony\Component\Yaml\Exception\RuntimeException;

class Database
{
    protected $driver;

    public function __construct($settings)
    {
        $this->initializeDriver($settings);
    }

    protected function initializeDriver($settings)
    {
        switch ($settings['driver'])
        {
            // TO REMOVE
            case 'redis':
                $this->driver = new RedisDatabaseDriver($settings['redis']);
                break;
            case 'mysql':
                $this->driver = new PdoDatabaseDriver($settings['mysql']);
                break;
            default:
                throw new RuntimeException('No database configured.');
                break;
        }
    }

    /* SCHEMA QUERIES */

    public function create(Table $table)
    {
        if ($this->driver instanceof RedisDatabaseDriver)
        {
            return;
        }

        $this->driver->create($table);
    }

    public function drop($tableName)
    {
        if ($this->driver instanceof RedisDatabaseDriver)
        {
            return;
        }

        $this->driver->drop($tableName);
    }

    public function dropAll()
    {
        $this->driver->dropAll();
    }

    /* QUERIES */

    public function table($table)
    {
        return $this->driver->table($table);
    }

    /* DEPRECATED */

    public function exec()
    {

    }

    public function prepare()
    {

    }

    public function beginTransaction()
    {
        $this->driver->beginTransaction();
    }

    public function commit()
    {
        $this->driver->commit();
    }

    public function rollBack()
    {
        $this->driver->rollBack();
    }
}
