<?php

namespace Library\Pyramid\Design\Templates\Sections;

use Library\Pyramid\Design\Rendering\Nodes\DivNode;
use Library\Pyramid\Design\Templates\Section;

class RootSection extends Section
{
    public function __construct(string $name)
    {
        $this->build($name);
    }

    private function build(string $name)
    {
        $this->node = new DivNode(1);
        $this->node->addClass(camelCaseToDash($name));
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return 'root';
    }

    /**
     * @param string $command
     * @return bool
     */
    public function acceptsCommand(string $command): bool
    {
        return false;
    }
}