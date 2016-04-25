<?php

namespace Library\DataMapper\Database;

use Library\DataMapper\Database\Drivers\MySqlDriver;

class QueryBuilder
{
    protected $databaseDriver;
    protected $table;
    protected $wheres = [];
    protected $sortingRules = [];
    protected $limit;

    public function __construct($config)
    {
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

    public function beginTransaction()
    {
        $this->databaseDriver->beginTransaction();
    }

    public function rollBack()
    {
        $this->databaseDriver->rollBack();
    }

    public function commit()
    {
        $this->databaseDriver->commit();
    }

    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    public function select(array $fields = ['*'])
    {
        $result = [];

        switch ($this->databaseDriver->name())
        {
            case 'mysql':
                $result = $this->selectMySql($fields);
                break;
        }

        $this->clear();

        return $result;
    }

    public function where($field, $operator, $value)
    {
        $this->addWhere($field, $operator, $value, 'AND');
        return $this;
    }

    public function orWhere($field, $operator, $value)
    {
        $this->addWhere($field, $operator, $value, 'OR');
        return $this;
    }

    protected function addWhere($field, $operator, $value, $link)
    {
        $this->wheres[] = [
            'var' => $field,
            'operator' => $operator,
            'value' => $value,
            'link' => $link
        ];
    }

    public function sortBy($field, $ascending = true)
    {
        $this->sortingRules[$field] = $ascending;

        return $this;
    }

    public function limit($number)
    {
        $this->limit = $number;

        return $this;
    }

    protected function selectMySql($fields)
    {
        $str = 'SELECT ';
        $str .= implode(', ', $fields);
        $str .= ' FROM '.$this->table;
        $str .= ' '.$this->databaseDriver->buildWheres($this->wheres);
        $str .= ' '.$this->databaseDriver->buildSorts($this->sortingRules);
        $str .= ' '.$this->databaseDriver->buildLimit($this->limit);

        if (sizeof($fields) == 1 && $fields[0] != '*')
        {
            return $this->databaseDriver->execute($str, 'SINGLE');
        }

        return $this->databaseDriver->execute($str);
    }

    public function insert(array $data)
    {
        // switch case driver...

        $str = 'INSERT INTO '.$this->table;
        $str .= ' ('.implode(',', array_keys($data)).')';

        $processed = [];
        foreach ($data as $key => $value)
        {
            $processed[':'.$key] = $value;
        }
        $data = $processed;

        $qString = '?';
        for ($i = 1; $i < sizeof($data); $i++)
        {
            $qString .= ',?';
        }
        $str .= ' VALUES('.$qString.')';

        $this->clear();

        return $this->databaseDriver->insert($str, $data);
    }

    public function insertMany(array $dataSet)
    {
        if (sizeof($dataSet) == 0)
        {
            return null;
        }

        $str = 'INSERT INTO '.$this->table;
        $str .= ' ('.implode(',', array_keys(reset($dataSet))).')';
        $str .= ' VALUES ';

        $segments = [];
        $qString = '?';
        for ($i = 1; $i < sizeof(reset($dataSet)); $i++)
        {
            $qString .= ',?';
        }
        foreach ($dataSet as $data)
        {
            $segments[] = '('.$qString.')';
        }
        $str .= implode(',', $segments);

        $this->clear();

        return $this->databaseDriver->insertMany($str, $dataSet);
    }

    public function update(array $data)
    {
        // switch case driver...

        $str = 'UPDATE '.$this->table.' SET ';

        $processed = [];
        $i = 0;
        foreach ($data as $key => $value)
        {
            if ($i > 0)
            {
                $str .= ', ';
            }

            $str .= $key.' = :'.$key;
            $processed[':'.$key] = $value;
            $i++;
        }

        $str .= ' '.$this->databaseDriver->buildWheres($this->wheres);

        $data = $processed;

        $this->clear();

        $this->databaseDriver->update($str, $data);
    }

    public function updateMany(array $updateSet, $idField)
    {
        $str = 'UPDATE '.$this->table.' SET';

        $setSegments = [];
        foreach ($updateSet as $field => $data)
        {
            $setSegmentStr = ' '.$field.' = CASE '.$idField;

            foreach ($data as $id => $value)
            {
                $setSegmentStr .= ' WHEN '.$id.' THEN "'.$value.'"';
            }

            $setSegmentStr .= ' ELSE '.$field.' END';

            $setSegments[] = $setSegmentStr;
        }
        $str .= implode(',', $setSegments);

        $str .= ' '.$this->databaseDriver->buildWheres($this->wheres);

        $this->clear();

        $this->databaseDriver->updateMany($str);
    }

    public function delete()
    {
        $str = 'DELETE FROM '.$this->table;
        $str .= ' '.$this->databaseDriver->buildWheres($this->wheres);

        $this->clear();

        return $this->databaseDriver->delete($str);
    }

    protected function clear()
    {
        $this->table = null;
        $this->wheres = [];
        $this->sortingRules = [];
        $this->limit = null;
    }
}