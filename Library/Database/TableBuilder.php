<?php

namespace Library\Database;

use Config\Tables;
use Symfony\Component\Yaml\Exception\RuntimeException;

class TableBuilder extends Tables
{
    protected $db;
    protected $tables;

    public function __construct($db)
    {
        $this->db = $db;

        if (env('APP_ENV') == 'framework_testing')
            $functions = get_class_methods('\\Tests\\FrameworkTest\\Config\\Tables');
        else
            $functions = get_class_methods('Config\\Tables');

        if (env('APP_ENV') == 'framework_testing')
        {
            $testTablesName = '\\Tests\\FrameworkTest\\Config\\Tables';
            $testTables = new $testTablesName();
            foreach ($functions as $function)
            {
                $table = $testTables->$function();
                $table->setTable($function);
                $this->tables[] = $table;
            }
        }
        else
        {
            foreach ($functions as $function)
            {
                $table = $this->$function();
                $table->setTable($function);
                $this->tables[] = $table;
            }
        }

        $this->buildTables();
    }

    public function getTable($modelName)
    {
        foreach ($this->tables as $table)
        {
            if (strcasecmp($table->modelName(), $modelName) == 0)
                return $table;
        }

        throw new RuntimeException('Table schema for '.$modelName.' does not exist.');
    }

    protected function buildTables()
    {
        foreach ($this->tables as $table)
            $this->buildTable($table);
    }

    protected function buildTable($table)
    {
        $sql = 'CREATE TABLE IF NOT EXISTS '.$table->tableName().' (';

        foreach ($table->columns() as $column)
            $sql .= $this->buildColumn($column);
        $sql = substr($sql, 0, -3);

        foreach ($table->columns() as $column)
        {
            if ($column->isUnique())
                $sql .= $this->buildUnique($column);
        }

        $sql .= ')';

        $this->db->query($sql);
    }

    protected function buildColumn($column)
    {
        $this->validateColumn($column);

        $str = $column->getName().' ';
        $str .= $this->getColumnTypeString($column->getType());
        if ($column->getSize() > 0)
            $str .= '('.$column->getSize().')';
        else if ($column->isPrimaryKey())
            $str .= '(11)';

        if ($column->isPrimaryKey())
        {
            $str .= ' UNSIGNED AUTO_INCREMENT PRIMARY KEY, ';
            return $str;
        }

        if (!$column->isNullable())
            $str .= ' NOT NULL';

        if (!is_null($column->getDefault()))
        {
            $str .= ' DEFAULT ';
            if (is_string($column->getDefault()))
            {
                $str .= '\''.$column->getDefault().'\'';
            }
            else
            {
                $str .= $column->getDefault();
            }
        }

        $str .= ',  ';
        return $str;
    }

    protected function buildUnique($column)
    {
        return ', UNIQUE ('.$column->getName().')';
    }

    protected function getColumnTypeString($type)
    {
        switch ($type)
        {
            case 'integer':
                return 'INT';
            case 'decimal':
                return 'DECIMAL';
            case 'string':
                return 'VARCHAR';
            case 'text':
                return 'TEXT';
            case 'boolean':
                return 'TINYINT';
            case 'datetime':
                return 'DATETIME';
            default:
                throw new RuntimeException('Unknown column type: '.$type);
                break;
        }
    }

    protected function validateColumn($column)
    {
        if ($column->isPrimaryKey() && $column->getType() != 'integer')
            throw new RuntimeException('Primary key '.$column->getName().' must be of integer type');
    }

    public function dropAllTables()
    {
        $result = false;
        foreach ($this->tables as $table)
        {
            $result = $this->dropTable($table->tableName()) && $result;
        }

        return $result;
    }

    public function dropTable($tableName)
    {
        $sql = 'DROP TABLE '.$tableName;
        return $this->db->exec($sql) > 0;
    }
}