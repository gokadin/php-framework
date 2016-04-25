<?php

namespace Library\DataMapper;

trait DataMapperPrimaryKey
{
    /** @Id */
    protected $id;

    public function getId()
    {
        return $this->id;
    }
}