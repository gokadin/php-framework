<?php

namespace Library\DataMapper\Database;

use Library\DataMapper\Database\Drivers\MySqlDriver;
use Library\DataMapper\Mapping\Drivers\AnnotationDriver;
use Library\DataMapper\Mapping\Metadata;

class SchemaTool
{
    protected $mappingDriver;
    protected $databaseDriver;
    protected $classes;

    public function __construct($config)
    {
        $this->classes = $config['classes'];

        $this->initializeMappingDriver($config['mappingDriver']);
        $this->initializeDatabaseDriver($config);
    }

    protected function initializeDatabaseDriver($config)
    {
        switch ($config['databaseDriver'])
        {
            default:
                $this->databaseDriver = new MySqlDriver($config[$config['databaseDriver']]);
                break;
        }
    }

    protected function initializeMappingDriver($driverName)
    {
        switch ($driverName)
        {
            default:
                $this->mappingDriver = new AnnotationDriver();
                break;
        }
    }

    public function create()
    {
        $results = [];

        foreach ($this->classes as $class)
        {
            $metadata = $this->mappingDriver->getMetadata($class);
            if ($this->databaseDriver->createTable($metadata))
            {
                $results[$metadata->table()] = true;
                continue;
            }

            $results[$metadata->table()] = false;
        }

        return $results;
    }

    public function drop()
    {
        foreach ($this->classes as $class)
        {
            $metadata = $this->mappingDriver->getMetadata($class);
            $this->dropTable($metadata->table());
        }
    }

    public function dropTable($table)
    {
        $this->databaseDriver->dropTable($table);
    }

    public function update($force = false)
    {
        $results = [];

        $tables = $this->databaseDriver->describeSchema();

        $configuredTableNames = [];
        foreach ($this->classes as $class)
        {
            $metadata = $this->mappingDriver->getMetadata($class);
            $configuredTableNames[] = $metadata->table();

            if (!isset($tables[$metadata->table()]))
            {
                $this->create($metadata);

                $results[$metadata->table()] = [
                    'status' => 'created'
                ];

                continue;
            }

            $columnResults = $this->updateColumns($metadata, $tables[$metadata->table()], $force);

            $foundChangedColumns = false;
            foreach ($columnResults as $columnResult)
            {
                if ($columnResult['status'] != 'unchanged')
                {
                    $foundChangedColumns = true;
                    break;
                }
            }
            if ($foundChangedColumns)
            {
                $results[$metadata->table()] = [
                    'status' => 'updated',
                    'columns' => $columnResults
                ];

                continue;
            }

            $results[$metadata->table()] = [
                'status' => 'unchanged'
            ];
        }

        foreach ($tables as $table => $columns)
        {
            if (!in_array($table, $configuredTableNames))
            {
                if (!$force)
                {
                    $results[$table] = [
                        'status' => 'unchanged'
                    ];

                    continue;
                }

                $this->dropTable($table);

                $results[$table] = [
                    'status' => 'dropped'
                ];
            }
        }

        return $results;
    }

    protected function updateColumns(Metadata $metadata, $existingColumns, $force)
    {
        $results = [];

        $configuredColumnNames = [];
        foreach ($metadata->columns() as $column)
        {
            $configuredColumnNames[] = $column->name();

            if (!isset($existingColumns[$column->name()]))
            {
                $this->databaseDriver->addColumnTo($metadata->table(), $column);

                $results[$column->name()] = [
                    'status' => 'created'
                ];

                continue;
            }

            if ($force &&
                (($column->type() != $existingColumns[$column->name()]['type'] && $column->type() != 'json') ||
                    ($column->type() == 'json' && $existingColumns[$column->name()]['type'] != 'text') ||
                    $column->isNullable() != $existingColumns[$column->name()]['isNullable'] ||
                    ($column->type() != 'datetime' &&
                        $column->type() != 'text' &&
                        $column->type() != 'boolean' &&
                        $column->type() != 'decimal' &&
                        $column->type() != 'json' &&
                        $column->size() != $existingColumns[$column->name()]['size']) ||
                    ($column->type() == 'decimal' &&
                        ($column->size() + 2 != $existingColumns[$column->name()]['size'] ||
                            $column->precision() != $existingColumns[$column->name()]['precision']))))
            {
                $this->databaseDriver->dropColumnFrom($metadata->table(), $column->name());
                $this->databaseDriver->addColumnTo($metadata->table(), $column);

                $results[$column->name()] = [
                    'status' => 'updated'
                ];

                continue;
            }

            $results[$column->name()] = [
                'status' => 'unchanged'
            ];
        }

        foreach ($existingColumns as $name => $data)
        {
            if (!in_array($name, $configuredColumnNames))
            {
                if (!$force)
                {
                    $results[$name] = [
                        'status' => 'unchanged'
                    ];

                    continue;
                }

                $this->databaseDriver->dropColumnFrom($metadata->table(), $name);

                $results[$name] = [
                    'status' => 'dropped'
                ];
            }
        }

        return $results;
    }
}