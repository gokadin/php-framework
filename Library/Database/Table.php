<?php

namespace Library\Database;

class Table
{
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $name;
    protected $columns = [];

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function name()
    {
        return $this->name;
    }

    public function increments($name)
    {
        $column = new Column($name, 'integer', 11);
        $column->addIndex();
        $column->primaryKey();
        return $this->columns[$name] = $column;
    }

    public function integer($name, $size = 11)
    {
        return $this->columns[$name] = new Column($name, 'integer', $size);
    }

    public function decimal($name, $size = 11, $precision = 2)
    {
        $column = new Column($name, 'decimal', $size);
        $column->precision($precision);
        return $this->columns[$name] = $column;
    }

    public function string($name, $size = 50)
    {
        return $this->columns[$name] = new Column($name, 'string', $size);
    }

    public function text($name)
    {
        return $this->columns[$name] = new Column($name, 'text', 0);
    }

    public function boolean($name)
    {
        return $this->columns[$name] = new Column($name, 'boolean', 1);
    }
    
    public function datetime($name)
    {
        return $this->columns[$name] = new Column($name, 'datetime', 0);
    }

    public function timestamps()
    {
        $this->columns[self::UPDATED_AT] = new Column(self::UPDATED_AT, 'datetime');
        $this->columns[self::CREATED_AT] = new Column(self::CREATED_AT, 'datetime');
    }

    public function columns()
    {
        return $this->columns;
    }

    public function column($columnName)
    {
        return isset($this->columns[$columnName]) ? $this->columns[$columnName] : null;
    }

    public function hasColumn($name)
    {
        foreach ($this->columns as $column)
        {
            if ($column->getName() == $name)
                return true;
        }

        return false;
    }

    public function hasTimestamps()
    {
        return $this->hasColumn(self::UPDATED_AT) && $this->hasColumn((self::CREATED_AT));
    }

    public function getPrimaryKey()
    {
        foreach ($this->columns as $column)
        {
            if ($column->isPrimaryKey())
            {
                return $column;
            }
        }

        return null;
    }
}