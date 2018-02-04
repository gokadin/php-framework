<?php

namespace Library\Pyramid\Config;

class ConfigReader extends Config
{
    public function read()
    {
        $config = [
            'rootDir' => $this->rootDir(),
            'clientAppDir' => $this->rootDir().'/src/app',
        ];

        $config = array_merge($config, $this->readModules());
        $config = array_merge($config, $this->readPages());

        return $config;
    }

    public function tempGetHtmlFile()
    {
        // veryyyyyyy temp

        $config = $this->read();

        $page = $config['selectedPage']['page'];

        $file = $config['clientAppDir'].'/pages/'.$page.'/'.$page.'.html';

        return $file;
    }

    public function tempGetScssFile()
    {
        // veryyyyyyy temp

        $config = $this->read();

        $page = $config['selectedPage']['page'];

        $file = $config['clientAppDir'].'/pages/'.$page.'/'.$page.'.scss';

        return $file;
    }

    private function readModules()
    {
        $modules = [];
        $selectedModule = '';

        foreach ($this->getLines($this->modulesFile()) as $line)
        {
            if ($this->isSelectedLine($line))
            {
                $line = substr($line, 0, -1);
                $selectedModule = $line;
            }

            $modules[] = $line;
        }

        return [
            'modules' => $modules,
            'selectedModule' => $selectedModule
        ];
    }

    private function readPages()
    {
        $pages = [];
        $selectedPage = '';

        foreach ($this->getLines($this->pagesFile()) as $line)
        {
            if ($this->isSelectedLine($line))
            {
                $line = substr($line, 0, -1);
                $parts = explode('@', $line);
                $selectedPage = [
                    'page' => $parts[0],
                    'module' => $parts[1]
                ];
            }

            $parts = explode('@', $line);
            $pages[] = [
                'page' => $parts[0],
                'module' => $parts[1]
            ];
        }

        return [
            'pages' => $pages,
            'selectedPage' => $selectedPage
        ];
    }
}