<?php

namespace Library\Engine\Queries;

class UpdateQuery extends EngineQuery
{
    /**
     * @param string $field
     * @param string $operator
     * @param string $value
     * @return UpdateQuery
     */
    public function where(string $field, string $operator, string $value): UpdateQuery
    {
        $this->queryBuilder->where($field, $operator, $value);
        return $this;
    }

    /**
     * @param string $field
     * @param string $operator
     * @param string $value
     * @return UpdateQuery
     */
    public function orWhere(string $field, string $operator, string $value): UpdateQuery
    {
        $this->queryBuilder->orWhere($field, $operator, $value);
        return $this;
    }
}