<?php

namespace Library\Pyramid\Generators;

use Library\Pyramid\Config\ConfigWriter;
use Library\Pyramid\FileEditors\FileEditor;
use Library\Pyramid\Templates\Client\ClientTemplates;

abstract class Generator
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var ClientTemplates
     */
    protected $templates;

    /**
     * @var FileEditor
     */
    protected $editor;

    /**
     * @var ConfigWriter
     */
    protected $configWriter;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->templates = new ClientTemplates();
        $this->editor = new FileEditor($config);
        $this->configWriter = new ConfigWriter($config);
    }

    protected function createFile(string $filename, string $content = '')
    {
        if (file_exists($filename))
        {
            throw new \Exception('File '.$filename.' already exists.');
        }

        file_put_contents($filename, $content);
    }

    protected function createDirectory(string $dirname)
    {
        if (file_exists($dirname))
        {
            return;
        }

        mkdir($dirname);
    }

    protected function getPageDir(bool $appendModulePath = true)
    {
        $pageDir = $this->config['clientAppDir'].'/pages';

        if ($appendModulePath && $this->config['selectedModule'] != 'app')
        {
            $pageDir .= '/'.str_replace('.', '/', $this->config['selectedModule']);
        }

        return $pageDir;
    }

    protected function getPageFile(string $pageName, bool $appendModulePath = true)
    {
        return $this->getPageDir($appendModulePath).'/'.$pageName;
    }

    protected function getComponentFile(string $componentName)
    {
        return $this->config['clientAppDir'].'/components/'.$componentName;
    }

    protected function getReducerFile(string $name)
    {
        return $this->config['clientAppDir'].'/reducers/'.$name.'.reducer.ts';
    }

    protected function getActionsFile(string $name)
    {
        return $this->config['clientAppDir'].'/actions/'.$name.'.actions.ts';
    }

    protected function getModuleDir(string $dotModule = '')
    {
        if ($dotModule == '')
        {
            $dotModule = $this->config['selectedModule'];
        }

        if ($dotModule == 'app')
        {
            return $this->config['clientAppDir'];
        }

        return $this->config['clientAppDir'].'/modules/'.substr(str_replace('.', '/', $dotModule), 0, strrpos($dotModule, '/'));
    }

    protected function getModuleName(string $dotModule = '')
    {
        if ($dotModule == '')
        {
            $dotModule = $this->config['selectedModule'];
        }

        return substr($dotModule, strrpos($dotModule, '/') + 1);
    }
}