<?php

namespace Library\Database\Drivers;

use Library\Database\Table;
use PDO;
use Symfony\Component\Yaml\Exception\RuntimeException;

class PdoDatabaseDriver implements IDatabaseDriver
{
    protected $dao;
    protected $databaseName;
    protected $table = null;
    protected $wheres = [];

    public function __construct($settings)
    {
        $this->databaseName = $settings['database'];
        $this->dao = new PDO('mysql:host='.$settings['host'].';dbname='.$this->databaseName,
            $settings['username'],
            $settings['password']);

        $this->dao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    public function insert(array $data)
    {
        $str = 'INSERT INTO '.$this->table;
        $str .= ' ('.implode(',', array_keys($data)).')';

        $processed = [];
        foreach ($data as $key => $value)
        {
            $processed[':'.$key] = $value;
        }
        $data = $processed;

        $str .= ' VALUES('.implode(',', array_keys($data)).')';

        $stmt = $this->dao->prepare($str);
        $stmt->execute($data);

        return $this->dao->lastInsertId();
    }

    public function where($var, $operator, $value = null)
    {
        $this->addWhere($var, $operator, $value, 'AND');
        return $this;
    }

    public function andWhere($var, $operator, $value = null)
    {
        $this->addWhere($var, $operator, $value, 'AND');
        return $this;
    }

    public function orWhere($var, $operator, $value = null)
    {
        $this->addWhere($var, $operator, $value, 'OR');
        return $this;
    }

    public function select(array $fields = ['*'])
    {
        $this->validateTable();

        $str = ' SELECT';
        $str .= ' '.implode(',', $fields);
        $str .= ' FROM '.$this->table;
        $str .= $this->buildWheres();

        $stmt = $this->dao->prepare($str);
        $stmt->execute();

        $this->clean();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update(array $data)
    {
        $this->validateTable();

        $str = 'UPDATE '.$this->table.' SET ';

        $i = 0;
        foreach ($data as $key => $value)
        {
            $i == 0 ? $i++ : $str .= ',';

            $value = '\''.$value.'\'';

            $str .= $key.' = '.$value;
        }

        $str .= $this->buildWheres();

        $stmt = $this->dao->prepare($str);
        $stmt->execute();

        $this->clean();
    }

    public function delete()
    {
        $this->validateTable();

        $str = 'DELETE FROM '.$this->table;

        $str .= $this->buildWheres();

        $this->clean();

        return $this->dao->exec($str);
    }

    public function dropAll()
    {
        $this->dao->exec('DROP DATABASE '.$this->databaseName);
        $this->dao->exec('CREATE DATABASE '.$this->databaseName);
    }

    public function drop($table)
    {
        $this->dao->exec('DROP TABLE '.$table);
    }

    public function create(Table $table)
    {
        $str = 'CREATE TABLE IF NOT EXISTS '.$table->name();

        $primaryKeyStr = '';
        $columnsStr = [];
        foreach ($table->columns() as $column)
        {
            if ($column->isPrimaryKey())
            {
                $primaryKeyStr = $column->getName();
                $primaryKeyStr .= ' '.$this->getColumnTypeString($column->getType());
                $primaryKeyStr .= '('.$column->getSize().')';
                $primaryKeyStr .= ' UNSIGNED AUTO_INCREMENT PRIMARY KEY';
                continue;
            }

            $columnStr = $column->getName();
            $columnStr .= ' '.$this->getColumnTypeString($column->getType());
            if ($column->getSize() > 0)
                $columnStr .= '('.$column->getSize().')';
            if (!$column->isNullable())
                $columnStr .= ' NOT NULL';
            if (!is_null($column->getDefault()))
            {
                $columnStr .= ' DEFAULT';
                is_string($column->getDefault())
                    ? $columnStr .= ' \''.$column->getDefault().'\''
                    : $columnStr .= ' '.$column->getDefault();
            }

            $columnsStr[] = $columnStr;
        }

        $str .= ' (';
        $str .= $primaryKeyStr.',';
        $str .= ' '.implode(', ', $columnsStr);

        foreach ($table->columns() as $column)
        {
            if ($column->isUnique())
            {
                $str .= ', UNIQUE ('.$column->getName().')';
            }
        }

        $str .= ')';

        return $this->dao->exec($str);
    }

    public function beginTransaction()
    {
        $this->dao->beginTransaction();
    }

    public function commit()
    {
        $this->dao->commit();
    }

    public function rollBack()
    {
        $this->dao->rollBack();
    }

    protected function addWhere($var, $operator, $value, $link)
    {
        if (is_null($value))
        {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'var' => $var,
            'operator' => $operator,
            'value' => $value,
            'link' => $link
        ];
    }

    protected function buildWheres()
    {
        if (sizeof($this->wheres) == 0)
        {
            return '';
        }

        $str = ' WHERE';

        for ($i = 0; $i < sizeof($this->wheres); $i++)
        {
            if ($i > 0)
            {
                $str .= ' '.$this->wheres[$i]['link'];
            }

            $str .= ' '.$this->wheres[$i]['var'];
            $str .= ' '.$this->wheres[$i]['operator'];

            $value = $this->wheres[$i]['value'];
            if (trim($this->wheres[$i]['operator']) != 'in')
            {
                if (substr($value, 0, 1) != '\'' && substr($value, -1) != '\'')
                {
                    $value = '\''.$value.'\'';
                }
            }

            $str .= ' '.$value;
        }

        return $str;
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

    protected function clean()
    {
        $this->table = null;
        $this->wheres = [];
    }

    protected function validateTable()
    {
        if (is_null($this->table))
        {
            throw new RuntimeException('PdoDatabaseDriver.Get : the table was not specified.');
        }
    }
}