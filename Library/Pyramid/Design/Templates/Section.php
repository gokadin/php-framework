<?php

namespace Library\Pyramid\Design\Templates;

use Library\Pyramid\Design\Rendering\Node;

abstract class Section
{
    /**
     * @var Node
     */
    protected $node;

    /**
     * @return Node
     */
    public function node(): Node
    {
        return $this->node;
    }

    /**
     * @return string
     */
    public abstract function name(): string;

    /**
     * @param string $command
     * @return bool
     */
    public abstract function acceptsCommand(string $command): bool;
}