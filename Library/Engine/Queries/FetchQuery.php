<?php

namespace Library\Engine\Queries;

use Library\DataMapper\Database\QueryBuilder;

class FetchQuery extends EngineQuery
{
    /**
     * FetchQuery constructor.
     *
     * @param QueryBuilder $queryBuilder
     */
    public function __construct(QueryBuilder $queryBuilder)
    {
        parent::__construct($queryBuilder);
    }

    /**
     * @param string $field
     * @param string $operator
     * @param string $value
     * @return FetchQuery
     */
    public function where(string $field, string $operator, string $value): FetchQuery
    {
        $this->queryBuilder->where($field, $operator, $value);
        return $this;
    }

    /**
     * @param string $field
     * @param string $operator
     * @param string $value
     * @return FetchQuery
     */
    public function orWhere(string $field, string $operator, string $value): FetchQuery
    {
        $this->queryBuilder->orWhere($field, $operator, $value);
        return $this;
    }
}