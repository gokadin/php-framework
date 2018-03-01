<?php

namespace Library\DataMapper;

trait DataMapperPrimaryKey
{
    /** @Id */
    private $id;

    public function getId()
    {
        return $this->id;
    }
}