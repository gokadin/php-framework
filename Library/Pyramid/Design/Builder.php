<?php

namespace Library\Pyramid\Design;

use Library\Pyramid\Config\ConfigReader;
use Library\Pyramid\Design\Rendering\Renderer;
use Library\Pyramid\Design\Templates\Section;
use Library\Pyramid\Design\Templates\Sections\RootSection;
use Library\Pyramid\Design\Templates\Sections\HeaderSection;

class Builder
{
    /**
     * @var ConfigReader
     */
    private $configReader;

    /**
     * @var Renderer
     */
    private $renderer;

    /**
     * @var Section
     */
    private $rootSection;

    /**
     * @var array
     */
    private $sections;

    /**
     * @var string
     */
    private $pointer;

    public function __construct()
    {
        $this->configReader = new ConfigReader();
        $this->renderer = new Renderer();

        $this->initRoot();
    }

    private function initRoot()
    {
        $this->rootSection = new RootSection('tpage');
        $this->sections = [$this->rootSection->name() => $this->rootSection];
        $this->pointer = $this->rootSection->name();
    }

    public function addHeader()
    {
        $this->addSection(new HeaderSection());

        $this->render();

        return 'Header added.';
    }

    public function setTitle(string $title)
    {
        if (!$this->currentSection()->acceptsCommand(DesignCommands::SET_TITLE))
        {
            return 'Current section does not have a title to set.';
        }

        $this->currentSection()->setTitle($title);

        $this->render();

        return 'Title set.';
    }

    private function addSection(Section $section): void
    {
        $this->currentSection()->node()->addChild($section->node());
        $this->sections[$section->name()] = $section;
        $this->pointer = $section->name();
    }

    private function render(): void
    {
        $htmlFile = $this->configReader->tempGetHtmlFile();
        $scssFile = $this->configReader->tempGetScssFile();

        file_put_contents($htmlFile, $this->renderer->render($this->rootSection->node()));
        file_put_contents($scssFile, $this->renderer->renderScss($this->rootSection->node()));
    }

    /**
     * @return Section
     */
    private function currentSection(): Section
    {
        return $this->sections[$this->pointer];
    }
}