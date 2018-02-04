<?php

namespace Library\Pyramid\Design\Rendering\Nodes;

use Library\Pyramid\Design\Rendering\Node;

class DivNode extends Node
{
    public function __construct(int $uid)
    {
        parent::__construct($uid);

        $this->type = self::DIV;
    }

    public function render(): string
    {
        $s = '<div';

        if (sizeof($this->classes()) > 0)
        {
            $s .= ' class="'.implode(' ', $this->classes()).'">';
        }

        return $s;
    }

    public function close(): string
    {
        return '</div>';
    }
}