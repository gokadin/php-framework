<?php
namespace Library\Pyramid\Generators\Client\Action;

use Library\Pyramid\Generators\Generator;

class ActionGenerator extends Generator
{
    public function generate(string $type, string $name, array $args = [])
    {
        $this->editor->addActionToActionsFile($type, $name, $args);
        $this->editor->addActionToReducer($type, $name);
        $this->configWriter->addAction($type, $name, $args);
    }
}