<?php

namespace Library\DataMapper\Mapping;

use Library\DataMapper\Mapping\Drivers\AnnotationDriver;
use ReflectionClass;

class Metadata
{
    const ASSOC_HAS_MANY = 'HasMany';
    const ASSOC_HAS_ONE = 'HasOne';
    const ASSOC_BELONGS_TO = 'BelongsTo';
    const CASCADE_DELETE = 'delete';
    const CASCADE_TOUCH = 'touch';

    protected $columns = [];
    protected $associations = [];
    protected $class;
    protected $table;
    protected $reflectionClass;
    protected $primaryKey;
    protected $createdAt;
    protected $updatedAt;

    public function __construct($class, $table, ReflectionClass $r)
    {
        $this->class = $class;
        $this->table = $table;
        $this->reflectionClass = $r;
    }

    public function className()
    {
        return $this->class;
    }

    public function table()
    {
        return $this->table;
    }

    public function getReflectionClass()
    {
        return $this->reflectionClass;
    }

    public function reflProp($propName)
    {
        $prop = $this->reflectionClass->getProperty($propName);
        $prop->setAccessible(true);
        return $prop;
    }

    public function primaryKey()
    {
        return $this->primaryKey;
    }

    public function hasCreatedAt()
    {
        return !is_null($this->createdAt);
    }

    public function createdAt()
    {
        return $this->createdAt;
    }

    public function hasUpdatedAt()
    {
        return !is_null($this->updatedAt);
    }

    public function updatedAt()
    {
        return $this->updatedAt;
    }

    public function columns()
    {
        return $this->columns;
    }

    public function getColumn($name)
    {
        return isset($this->columns[$name]) ? $this->columns[$name] : null;
    }

    public function getColumnByPropName($propName)
    {
        foreach ($this->columns as $column)
        {
            if ($column->propName() == $propName)
            {
                return $column;
            }
        }

        return null;
    }

    public function addColumn(Column $column)
    {
        $this->columns[$column->name()] = $column;

        if ($column->isPrimaryKey())
        {
            $this->primaryKey = $column;
        }

        if ($column->isCreatedAt())
        {
            $this->createdAt = $column;
        }

        if ($column->isUpdatedAt())
        {
            $this->updatedAt = $column;
        }
    }

    public function associations()
    {
        return $this->associations;
    }

    public function getAssociation($propName)
    {
        return $this->associations[$propName];
    }

    public function hasAssociation()
    {
        return sizeof($this->associations) > 0;
    }

    public function addHasOneAssociation($columnName, $propName, $target, $cascades, $nullable, $load)
    {
        $column = new Column(lcfirst($columnName).'_id', $propName, 'integer', AnnotationDriver::DEFAULT_INTEGER_SIZE);
        $column->setForeignKey();
        $column->setNullable();
        //$this->columns[$columnName] = $column;

        $this->associations[$propName] = new Association(
            $column, self::ASSOC_HAS_ONE, $target, $propName, $cascades, $nullable, null, $load);
    }

    public function addBelongsToAssociation($columnName, $propName, $target, $cascades, $nullable, $load)
    {
        $column = new Column(lcfirst($columnName).'_id', $propName, 'integer', AnnotationDriver::DEFAULT_INTEGER_SIZE);
        $column->setForeignKey();
        $column->setNullable();
        $this->columns[$columnName] = $column;

        $this->associations[$propName] = new Association(
            $column, self::ASSOC_BELONGS_TO, $target, $propName, $cascades, $nullable, null, $load);
    }

    public function addHasManyAssociation($propName, $target, $cascades, $nullable, $mappedBy)
    {
        $this->associations[$propName] = new Association(
            null, self::ASSOC_HAS_MANY, $target, $propName, $cascades, $nullable, $mappedBy);
    }

    public function generateForeignKeyName()
    {
        return lcfirst($this->reflectionClass->getShortName()).'_id';
    }
}