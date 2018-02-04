<?php

namespace Library\Pyramid\Generators\Client\Page;

use Library\Pyramid\Generators\Generator;

class PageGenerator extends Generator
{
    public function generate(string $pageName)
    {
        $this->createComponent($pageName);
        $this->configWriter->addPage($pageName);
        $this->configWriter->selectPage($pageName);
        $this->editor->addPageDeclaration($pageName);
        $this->editor->addPageRoute($pageName);
    }

    private function createComponent(string $pageName)
    {
        $pageFile = $this->getPageFile($pageName);

        $this->createDirectory($pageFile);

        $scss = $this->templates->emptyScss($pageName);

        $html = $this->templates->emptyHtml($pageName);

        $ts = $this->templates->importFrom(['Component'], '@angular/core').PHP_EOL;
        $ts .= $this->templates->componentDeclaration($pageName);
        $ts .= $this->templates->emptyClass($pageName);

        $this->createFile($pageFile.'/'.$pageName.'.scss', $scss);
        $this->createFile($pageFile.'/'.$pageName.'.html', $html);
        $this->createFile($pageFile.'/'.ucfirst($pageName).'.ts', $ts);
    }
}