<?php

namespace Library\Engine\Queries;

class DeleteQuery extends EngineQuery
{
    /**
     * @param string $field
     * @param string $operator
     * @param string $value
     * @return DeleteQuery
     */
    public function where(string $field, string $operator, string $value): DeleteQuery
    {
        $this->queryBuilder->where($field, $operator, $value);
        return $this;
    }

    /**
     * @param string $field
     * @param string $operator
     * @param string $value
     * @return DeleteQuery
     */
    public function orWhere(string $field, string $operator, string $value): DeleteQuery
    {
        $this->queryBuilder->orWhere($field, $operator, $value);
        return $this;
    }
}