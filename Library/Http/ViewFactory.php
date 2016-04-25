<?php

namespace Library\Http;

use Library\Container\Container;

class ViewFactory
{
    private $container;
    protected $layoutFile;
    protected $sections;
    protected $currentSectionName;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->layoutFile = null;
        $this->sections = array();
        $this->currentSectionName = null;
    }

    public function setLayoutFile($fileName)
    {
        $this->layoutFile = $fileName;
    }

    public function hasLayout()
    {
        return !is_null($this->layoutFile);
    }

    public function getLayoutFile()
    {
        return $this->layoutFile;
    }

    public function startSection($name)
    {
        $this->currentSectionName = $name;

        ob_start();
    }

    public function endSection()
    {
        if (is_null($this->currentSectionName))
        {
            return;
        }

        $content = ob_get_clean();

        $this->sections[$this->currentSectionName] = $content;
    }

    public function getSection($name)
    {
        if (isset($this->sections[$name]))
        {
            return $this->sections[$name];
        }
    }

    public function inject($class)
    {
        if (substr($class, 0, 1) == '\\')
        {
            $class = substr($class, 1);
        }

        return $this->container->resolve($class);

    }
}