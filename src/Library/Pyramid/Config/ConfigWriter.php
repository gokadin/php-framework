<?php

namespace Library\Pyramid\Config;

class ConfigWriter extends Config
{
    /**
     * @var array
     */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function addAction(string $type, string $name, array $args = [])
    {
        file_put_contents($this->actionsFile(), $name.'@'.$type, FILE_APPEND);

        file_put_contents($this->configDir().'/client/actions/'.$name.'@'.$type.'.parg', '');
    }

    public function addReducer(string $name)
    {
        file_put_contents($this->reducersFile(), $name, FILE_APPEND);
    }

    public function addComponent(string $name, string $pageName = '')
    {
        if ($pageName == '')
        {
            $pageName = $this->config['selectedPage']['page'];
        }

        file_put_contents($this->componentsFile(), $name.'@'.$pageName, FILE_APPEND);

        file_put_contents($this->configDir().'/client/components/'.$name.'@'.$pageName.'.phtml', '');
    }

    public function addPage(string $pageName, string $dotModule = '')
    {
        if ($dotModule == '')
        {
            $dotModule = $this->config['selectedModule'];
        }

        file_put_contents($this->pagesFile(), $pageName.'@'.$dotModule, FILE_APPEND);

        file_put_contents($this->configDir().'/client/pages/'.$pageName.'@'.$dotModule.'.phtml', '');
    }

    public function selectPage(string $pageName, string $dotModule = '')
    {
        if ($dotModule == '')
        {
            $dotModule = $this->config['selectedModule'];
        }

        $this->select($this->pagesFile(), $pageName.'@'.$dotModule);
    }

    public function selectComponent(string $name, string $pageName = '')
    {
        if ($pageName == '')
        {
            $pageName = $this->config['selectedPage']['page'];
        }

        $this->select($this->componentsFile(), $name.'@'.$pageName);
    }

    public function selectReducer(string $name)
    {
        $this->select($this->reducersFile(), $name);
    }

    private function select(string $file, string $line)
    {
        $content = file_get_contents($file);

        $content = str_replace('*', '', $content);
        $content = str_replace($line, $line.'*', $content);

        file_put_contents($file, $content);
    }
}