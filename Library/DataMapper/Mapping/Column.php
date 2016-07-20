<?php

namespace Library\DataMapper\Mapping;

class Column
{
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';

    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'integer';
    const TYPE_TEXT = 'text';
    const TYPE_DECIMAL = 'decimal';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_DATETIME = 'datetime';
    const TYPE_JSON = 'json';

    protected $columnName;
    protected $propName;
    protected $isPrimaryKey;
    protected $isForeignKey;
    protected $type;
    protected $size;
    protected $precision;
    protected $isNullable;
    protected $isUnique;
    protected $defaultValue;
    protected $hasIndex;

    public function __construct($columnName, $propName, $type, $size)
    {
        $this->columnName = $columnName;
        $this->propName = $propName;
        $this->type = $type;
        $this->size = $size;
        $this->isNullable = false;
        $this->precision = 2;
        $this->isPrimaryKey = false;
        $this->isForeignKey = false;
        $this->isUnique = false;
        $this->defaultValue = null;
        $this->hasIndex = false;
    }

    public function name()
    {
        return $this->columnName;
    }

    public function type()
    {
        return $this->type;
    }

    public function isBoolean()
    {
        return $this->type == self::TYPE_BOOLEAN;
    }

    public function isDateTime()
    {
        return $this->type == self::TYPE_DATETIME;
    }

    public function isPrimaryKey()
    {
        return $this->isPrimaryKey;
    }

    public function setPrimaryKey()
    {
        $this->isPrimaryKey = true;
    }

    public function isForeignKey()
    {
        return $this->isForeignKey;
    }

    public function setForeignKey()
    {
        $this->isForeignKey = true;
    }

    public function isTimeStamp()
    {
        return $this->columnName == self::CREATED_AT ||
            $this->columnName == self::UPDATED_AT;
    }

    public function isCreatedAt()
    {
        return $this->columnName == self::CREATED_AT;
    }

    public function isUpdatedAt()
    {
        return $this->columnName == self::UPDATED_AT;
    }

    public function propName()
    {
        return $this->propName;
    }

    public function isNullable()
    {
        return $this->isNullable;
    }

    public function setNullable()
    {
        $this->isNullable = true;
    }

    public function size()
    {
        return $this->size;
    }

    public function isUnique()
    {
        return $this->isUnique;
    }

    public function unique()
    {
        $this->isUnique = true;
    }

    public function defaultValue()
    {
        return $this->defaultValue;
    }

    public function isDefault()
    {
        return !is_null($this->defaultValue);
    }

    public function setDefaultValue($value)
    {
        $this->defaultValue = $value;
    }

    public function precision()
    {
        return $this->precision;
    }

    public function setPrecision($precision)
    {
        $this->precision = $precision;
    }

    public function hasIndex()
    {
        return $this->hasIndex;
    }

    public function setIndex()
    {
        $this->hasIndex = true;
    }

    public function isJson()
    {
        return $this->type == self::TYPE_JSON;
    }

    public function isRequired()
    {
        if ($this->columnName === self::UPDATED_AT ||
            $this->columnName === self::CREATED_AT)
            return false;

        return !$this->isNullable() && !$this->isDefault();
    }
}