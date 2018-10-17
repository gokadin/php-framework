<?php

namespace Library\Engine;

class EngineDataParser
{
    private const WHERE_KEY = 'where';
    private const OR_WHERE_KEY = 'orWhere';
    private const SORT_KEY = 'sort';
    private const LIMIT_KEY = 'limit';

    /**
     * @var Engine
     */
    private $engine;

    /**
     * EngineRequestExecutor constructor.
     *
     * @param Engine $engine
     */
    public function __construct(Engine $engine)
    {
        $this->engine = $engine;
    }

    /**
     * @param array $data
     */
    public function parse(array $data): void
    {
        foreach ($data as $action => $types)
        {
            $this->parseAction($action, $types);
        }
    }

    /**
     * @param string $action
     * @param array $types
     */
    private function parseAction(string $action, array $types): void
    {
        switch (strtoupper($action))
        {
            case Engine::FETCH_KEY:
                $this->parseFetchTypes($types);
                break;
            case Engine::CREATE_KEY:
                $this->parseCreateTypes($types);
                break;
            case Engine::UPDATE_KEY:
                $this->parseUpdateTypes($types);
                break;
            case Engine::DELETE_KEY:
                $this->parseDeleteTypes($types);
                break;
        }
    }

    /**
     * @param array $types
     */
    private function parseFetchTypes(array $types): void
    {
        foreach ($types as $type => $data)
        {
            $this->parseFetchType($type, $data);
        }
    }

    /**
     * @param string $type
     * @param array $data
     */
    private function parseFetchType(string $type, array $data): void
    {
        $query = $this->engine->fetch($type, $data['fields']);

        if (isset($data['conditions']))
        {
            $this->addQueryConditions($query, $data['conditions']);
        }
    }

    /**
     * @param array $types
     */
    private function parseCreateTypes(array $types): void
    {
        foreach ($types as $type => $data)
        {
            $this->parseCreateType($type, $data);
        }
    }

    /**
     * @param string $type
     * @param array $data
     */
    private function parseCreateType(string $type, array $data): void
    {
        if (isset($data['fields']))
        {
            $this->engine->create($type, $data['values'], $data['fields']);

            return;
        }

        $this->engine->create($type, $data['values']);
    }

    /**
     * @param array $types
     */
    private function parseUpdateTypes(array $types): void
    {
        foreach ($types as $type => $data)
        {
            $this->parseUpdateType($type, $data);
        }
    }

    /**
     * @param string $type
     * @param array $data
     */
    private function parseUpdateType(string $type, array $data): void
    {
        $query = isset($data['fields'])
            ? $this->engine->update($type, $data['values'], $data['fields'])
            : $this->engine->update($type, $data['values']);

        if (isset($data['conditions']))
        {
            $this->addQueryConditions($query, $data['conditions']);
        }
    }

    /**
     * @param array $types
     */
    private function parseDeleteTypes(array $types): void
    {
        foreach ($types as $type => $data)
        {
            $this->parseDeleteType($type, $data);
        }
    }

    /**
     * @param string $type
     * @param array $data
     */
    private function parseDeleteType(string $type, array $data): void
    {
        $query = $this->engine->delete($type);

        if (isset($data['conditions']))
        {
            $this->addQueryConditions($query, $data['conditions']);
        }
    }

    /**
     * @param $query
     * @param array $conditions
     */
    private function addQueryConditions($query, array $conditions): void
    {
        foreach ($conditions as $condition)
        {
            $this->addQueryCondition($query, $condition);
        }
    }

    /**
     * @param $query
     * @param string $key
     * @param $condition
     */
    private function addQueryCondition($query, $condition): void
    {
        if ($condition[0] == self::WHERE_KEY || $condition[0] == self::OR_WHERE_KEY && substr($condition[1], -2) == 'Id')
        {
            $condition[1] = str_replace('Id', '_id', $condition[1]);
        }

        switch ($condition[0])
        {
            case self::WHERE_KEY:
                $query->where($condition[1], $condition[2], $condition[3]);
                break;
            case self::OR_WHERE_KEY:
                $query->orWhere($condition[1], $condition[2], $condition[3]);
                break;
            case self::SORT_KEY:
                $query->sort($condition[1], strtoupper($condition[2]) == 'ASC');
                break;
            case self::LIMIT_KEY:
                $query->limit($condition[1]);
                break;
        }
    }
}