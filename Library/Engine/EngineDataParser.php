<?php

namespace Library\Engine;

class EngineDataParser
{
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
     * @return array
     */
    public function parse(array $data): array
    {
        
    }
}