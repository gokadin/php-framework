<?php

namespace Library\Pyramid\Design\Templates\Sections;

use Library\Pyramid\Design\DesignCommands;
use Library\Pyramid\Design\Rendering\Nodes\DivNode;
use Library\Pyramid\Design\Templates\Section;

class HeaderSection extends Section
{
    /**
     * @var array
     */
    private $acceptedCommands = [
        DesignCommands::SET_TITLE
    ];

    public function __construct()
    {
        $this->build();
    }

    private function build(): void
    {
        $this->node = new DivNode(2);
        $this->node->addClass('header');
        $this->node->addCssRule('display', 'flex');
        $this->node->addCssRule('align-items', 'center');
        $this->node->addCssRule('height', '40px');
        $this->node->addCssRule('background-color', '#444');

        $title = new DivNode(3);
        $title->addClass('title');
        $title->addCssRule('flex', '1');
        $title->addCssRule('color', '#fff');
        $this->node->addChild($title);

        $controls = new DivNode(4);
        $controls->addClass('controls');
        $controls->addCssRule('flex', '1');
        $this->node->addChild($controls);
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return 'header';
    }

    /**
     * @param string $command
     * @return bool
     */
    public function acceptsCommand(string $command): bool
    {
        return in_array($command, $this->acceptedCommands);
    }

    public function setTitle(string $title)
    {
        $this->node->child(3)->setText($title);
    }
}