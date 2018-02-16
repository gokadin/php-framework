<?php

namespace Library\Engine\Queries;

class FetchQuery extends EngineQuery
{
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

    /**
     * @param string $field
     * @param bool $ascending
     * @return FetchQuery
     */
    public function sort(string $field, bool $ascending = true): FetchQuery
    {
        $this->queryBuilder->sortBy($field, $ascending);
        return $this;
    }

    /**
     * @param int $limit
     * @return FetchQuery
     */
    public function limit(int $limit): FetchQuery
    {
        $this->queryBuilder->limit($limit);
        return $this;
    }
}