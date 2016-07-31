<?php

namespace Library\Database;

class Column
{
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

    protected $name;
    protected $propertyName;
    protected $isPrimaryKey;
    protected $type;
    protected $size;
    protected $precision;
    protected $canBeNull;
    protected $isUnique;
    protected $default;
    protected $hasIndex;

    public function __construct($name, $type, $size = -1)
    {
        $this->name = $name;
        $this->propertyName = null;
        $this->type = $type;
        $this->canBeNull = false;
        $this->size = $size;
        $this->precision = 2;
        $this->isPrimaryKey = false;
        $this->isUnique = false;
        $this->default = null;
        $this->hasIndex = false;
    }

    public function primaryKey()
    {
        $this->isPrimaryKey = true;
        return $this;
    }

    public function propertyName($name)
    {
        $this->propertyName = $name;
    }

    public function nullable()
    {
        $this->canBeNull = true;
        return $this;
    }

    public function size($value)
    {
        $this->size = $value;
        return $this;
    }

    public function unique()
    {
        $this->isUnique = true;
        return $this;
    }

    public function _default($value)
    {
        $this->default = $value;
        return $this;
    }

    public function precision($precision)
    {
        $this->precision = $precision;
        return $this;
    }

    public function __call($name, $args)
    {
        if ($name === 'default' && sizeof($args) == 1)
            return $this->_default($args[0]);
    }

    public function addIndex()
    {
        $this->hasIndex = true;
        return $this;
    }

    public function getPropertyName()
    {
        return $this->propertyName;
    }

    public function getName()
    {
        return $this->name;
    }

    public function isPrimaryKey()
    {
        return $this->isPrimaryKey;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getPrecision()
    {
        return $this->precision;
    }

    public function isNullable()
    {
        return $this->canBeNull;
    }

    public function isRequired()
    {
        if ($this->name === self::UPDATED_AT ||
            $this->name === self::CREATED_AT)
            return false;

        return !$this->isNullable() && !$this->isDefault();
    }

    public function isUnique()
    {
        return $this->isUnique;
    }

    public function isDefault()
    {
        return !is_null($this->default);
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function hasIndex()
    {
        return $this->hasIndex;
    }
}