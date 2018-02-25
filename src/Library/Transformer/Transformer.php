<?php

namespace Library\Transformer;

class Transformer
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var array
     */
    private $definitions = [];

    /**
     * @var array
     */
    private $only = [];

    /**
     * @var array
     */
    private $without = [];

    private $with = [];

    public function __construct($config)
    {
        $this->registerDefinitions($config);
    }

    /**
     * @param $config
     */
    private function registerDefinitions($config)
    {
        $this->definitions = $config;
    }

    /**
     * @param $class string
     * @return $this Transformer
     * @throws TransformerException
     */
    public function of($class)
    {
        if (!isset($this->definitions[$class]))
        {
            throw new TransformerException('Transformation for class '.$class.' is not defined.');
        }

        $this->class = $class;

        return $this;
    }

    /**
     * @param array|object
     * @return array|null
     */
    public function transform($items)
    {
        $result = is_array($items)
            ? $this->transformCollection($items)
            : $this->transformSingle($items);

        $this->clear();

        return $result;
    }

    /**
     * @param array $items
     * @return array
     */
    private function transformCollection(array $items)
    {
        return array_map([$this, 'transformSingle'], $items);
    }

    /**
     * @param $item
     * @return array
     */
    private function transformSingle($item)
    {
        $result = [];

        foreach ($this->definitions[$this->class] as $key => $closure)
        {
            if (sizeof($this->only) > 0 && !in_array($key, $this->only))
            {
                continue;
            }

            if (sizeof($this->without) > 0 && in_array($key, $this->without))
            {
                continue;
            }

            $result[$key] = $closure($item);
        }

        if (sizeof($this->with) > 0)
        {
            $this->executeWith($item, $result);
        }

        return $result;
    }

    private function executeWith($item, $result)
    {
        foreach ($this->with as $key => $closure)
        {
            $result[$key] = $closure($item);
        }
    }

    /**
     * @param array $only
     * @return $this Transformer
     */
    public function only(array $only)
    {
        $this->only = $only;

        return $this;
    }

    public function without(array $without)
    {
        $this->without = $without;

        return $this;
    }

    public function with(array $with)
    {
        $this->with[] = $with;

        return $this;
    }

    /**
     * Clear the transformer for the next call
     */
    private function clear()
    {
        $this->class = null;
        $this->only = [];
        $this->without = [];
        $this->with = [];
    }
}