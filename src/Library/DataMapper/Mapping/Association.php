<?php

namespace Library\DataMapper\Mapping;

class Association
{
    const LOAD_ALWAYS = 'LOAD_ALWAYS';
    const LOAD_LAZY = 'LOAD_LAZY';

    /**
     * @var Column
     */
    private $column;

    private $type;

    private $target;

    private $propName;

    /**
     * @var array
     */
    private $cascades = [];

    private $isNullable;

    private $mappedBy;

    private $load;

    public function __construct($column, $type, $target, $propName, array $cascades, $isNullable,
                                $mappedBy = null, $load = 'LOAD_LAZY')
    {
        $this->column = $column;
        $this->type = $type;
        $this->target = $target;
        $this->propName = $propName;
        $this->cascades = $cascades;
        $this->isNullable = $isNullable;
        $this->mappedBy = $mappedBy;
        $this->load = $load;
    }

    public function column()
    {
        return $this->column;
    }

    public function type()
    {
        return $this->type;
    }

    public function target()
    {
        return $this->target;
    }

    public function propName()
    {
        return $this->propName;
    }

    public function cascades()
    {
        return $this->cascades;
    }

    public function isNullable()
    {
        return $this->isNullable;
    }

    public function mappedBy()
    {
        return $this->mappedBy;
    }

    public function isLazy()
    {
        return $this->load == self::LOAD_LAZY;
    }

    public function hasDeleteCascade()
    {
        foreach ($this->cascades as $cascade)
        {
            if ($cascade == Metadata::CASCADE_DELETE)
            {
                return true;
            }
        }

        return false;
    }

    public function hasTouchCascade()
    {
        foreach ($this->cascades as $cascade)
        {
            if ($cascade == Metadata::CASCADE_TOUCH)
            {
                return true;
            }
        }

        return false;
    }
}