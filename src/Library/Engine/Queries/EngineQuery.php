<?php

namespace Library\Engine\Queries;

use Library\DataMapper\Database\QueryBuilder;

abstract class EngineQuery
{
    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }
}