<?php

namespace Library\Pyramid\Config;

abstract class Config
{
    private $rootDir = __DIR__.'/../../..';

    protected function rootDir()
    {
        return $this->rootDir;
    }

    protected function configDir()
    {
        return $this->rootDir.'/.pyramid';
    }

    protected function modulesFile()
    {
        return $this->configDir().'/client/modules.pyr';
    }

    protected function pagesFile()
    {
        return $this->configDir().'/client/pages.pyr';
    }

    protected function componentsFile()
    {
        return $this->configDir().'/client/components.pyr';
    }

    protected function reducersFile()
    {
        return $this->configDir().'/client/reducers.pyr';
    }

    protected function actionsFile()
    {
        return $this->configDir().'/client/actions.pyr';
    }

    protected function getLines(string $filePath)
    {
        $lines = [];

        $content = fopen($filePath, 'r');
        if ($content) {
            while (($line = fgets($content)) !== false) {
                $lines[] = trim(str_replace(PHP_EOL, '', $line));
            }

            fclose($content);
        }

        return $lines;
    }

    protected function isSelectedLine(string $line)
    {
        return substr($line, -1) == '*';
    }
}