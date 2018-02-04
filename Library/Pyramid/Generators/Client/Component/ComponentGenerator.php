<?php

namespace Library\Pyramid\Generators\Client\Component;

use Library\Pyramid\Generators\Generator;

class ComponentGenerator extends Generator
{
    public function generate(string $name)
    {
        $this->createComponent($name);
        $this->configWriter->addComponent($name);
        $this->configWriter->selectComponent($name);
        $this->editor->addComponentDeclaration($name);
    }

    private function createComponent(string $name)
    {
        $componentFile = $this->getComponentFile($name);

        $this->createDirectory($componentFile);

        $scss = $this->templates->emptyScss($name);

        $html = $this->templates->emptyHtml($name);

        $ts = $this->templates->importFrom(['Component'], '@angular/core').PHP_EOL;
        $ts .= $this->templates->componentDeclaration($name);
        $ts .= $this->templates->emptyClass($name);

        $this->createFile($componentFile.'/'.$name.'.scss', $scss);
        $this->createFile($componentFile.'/'.$name.'.html', $html);
        $this->createFile($componentFile.'/'.ucfirst($name).'.ts', $ts);
    }
}