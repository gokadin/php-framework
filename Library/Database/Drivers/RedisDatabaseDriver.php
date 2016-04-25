<?php

namespace Library\Database\Drivers;

use Library\Database\Table;
use Predis\Client as PredisClient;

class RedisDatabaseDriver implements IDatabaseDriver
{
    const NEXT_ID = '_nextId';
    const ID = '_id';
    const IDS = '_ids';
    const SCHEMA = '_SCHEMA';
    const COLUMNS = '_columns';
    const COLUMN = '_column';

    protected $redis;
    protected $table;
    protected $wheres = [];

    public function __construct($settings)
    {
        $this->redis = new PredisClient([
            'database' => $settings['database']
        ]);
    }

    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    public function create(Table $table)
    {
        $columnNames = [];
        foreach ($table->columns() as $column)
        {
            $columnNames[] = $column->getName();
        }

        $this->redis->sadd(self::SCHEMA.':'.$table->name().':columns', $columnNames);

        foreach ($table->columns() as $column)
        {
            $this->redis->hmset(self::SCHEMA.':'.$table->name().':column:'.$column->getName(), [
                'isPrimaryKey' => $column->isPrimaryKey() ? 1 : 0,
                'type' => $column->getType(),
                'size' => $column->getSize(),
                'precision' => $column->getPrecision(),
                'isNullable' => $column->isNullable() ? 1 : 0,
                'isRequired' => $column->isRequired() ? 1 : 0,
                'isUnique' => $column->isUnique() ? 1 : 0,
                'isDefault' => $column->isDefault() ? 1 : 0,
                'defaultValue' => $column->getDefault(),
                'hasIndex' => $column->hasIndex() ? 1 : 0
            ]);
        }
    }

    protected function accessTable($table)
    {
        $t = new Table($table);

        $columnNames = $this->redis->smembers(self::SCHEMA.':'.$table.':columns');

        foreach ($columnNames as $columnName)
        {
            $columnProperties = $this->redis->hgetall(self::SCHEMA.':'.$table.':column:'.$columnName);

            if ($columnProperties['isPrimaryKey'])
            {
                $t->increments($columnName);
                continue;
            }

            $column = null;
            switch ($columnProperties['type'])
            {
                case 'integer':
                    $column = $t->integer($columnName);
                    break;
                case 'string':
                    $column = $t->string($columnName);
                    break;
                case 'text':
                    $column = $t->text($columnName);
                    break;
                case 'decimal':
                    $column = $t->decimal($columnName);
                    break;
                case 'boolean':
                    $column = $t->boolean($columnName);
                    break;
                case 'datetime':
                    $column = $t->datetime($columnName);
                    break;
            }

            if (is_null($column))
            {
                continue;
            }

            $column = $column->size($columnProperties['size']);
            $column = $column->precision($columnProperties['precision']);
            if ($columnProperties['isNullable'] == 1)
            {
                $column = $column->nullable();
            }
            if ($columnProperties['isDefault'] == 1)
            {
                $column = $column->default($columnProperties['defaultValue']);
            }
            if ($columnProperties['isUnique'] == 1)
            {
                $column = $column->unique();
            }
            if ($columnProperties['hasIndex'] == 1)
            {
                $column->addIndex();
            }
        }

        return $t;
    }

    public function insert(array $data)
    {
        $id = $this->getNextId($this->table);

        $this->redis->hmset($this->table.':'.self::ID.':'.$id, $data);

        $this->insertIndexedColumns($this->accessTable($this->table), $data, $id);

        $this->clean();

        return $id;
    }

    public function dropAll()
    {
        $this->redis->flushdb();
    }

    protected function getNextId($key)
    {
        return $this->redis->incr(self::NEXT_ID.':'.$key);
    }

    protected function insertIndexedColumns($table, $data, $id)
    {
        foreach ($table->columns() as $column)
        {
            if ($column->isPrimaryKey())
            {
                $this->redis->sadd($table->name().':'.self::IDS, $id);
                continue;
            }

            if ($column->hasIndex())
            {
                $this->redis->sadd($table->name().':'.$column->getName().':'.$data[$column->getName()], $id);
            }
        }
    }

    protected function clean()
    {
        $this->table = null;
        $this->wheres = [];
    }

    public function select(array $data)
    {
        // ...

        $this->clean();
    }

    public function update(array $data)
    {
        // TODO: Implement update() method.
    }

    public function delete()
    {
        // TODO: Implement delete() method.
    }

    public function beginTransaction()
    {
        // TODO: Implement beginTransaction() method.
    }

    public function commit()
    {
        // TODO: Implement commit() method.
    }

    public function rollBack()
    {
        // TODO: Implement rollBack() method.
    }
}