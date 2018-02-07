<?php

namespace Library\Pyramid\FileEditors;

use Library\Pyramid\Templates\Client\ClientTemplates;

class FileEditor
{
    private $tab = '    ';

    /**
     * @var array
     */
    private $config;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var ClientTemplates
     */
    private $clientTemplates;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->parser = new Parser();
        $this->clientTemplates = new ClientTemplates();
    }

    public function addPageDeclaration(string $name, string $dotModule = '')
    {
        if ($dotModule == '')
        {
            $dotModule = $this->config['selectedModule'];
        }

        $file = $this->getModuleFile($dotModule).'.declarations.ts';

        $importPath = $this->getPageImport($name, $dotModule);

        $content = file_get_contents($file);

        $importsEnd = strrpos($content, '\';') + 3;

        $edit = substr($content, 0, $importsEnd);
        $edit .= $this->clientTemplates->importFrom([ucfirst($name)], $importPath);
        $edit .= substr($content, $importsEnd);

        $content = $edit;
        $declarationsEnd = strrpos($content, ']');

        $edit = substr($content, 0, $declarationsEnd);
        $edit .= $this->tab.ucfirst($name).','.PHP_EOL;
        $edit .= substr($content, $declarationsEnd);

        file_put_contents($file, $edit);
    }

    public function addPageRoute(string $name, string $dotModule = '')
    {
        if ($dotModule == '')
        {
            $dotModule = $this->config['selectedModule'];
        }

        $file = $this->getModuleFile($dotModule).'.routing.ts';

        $importPath = $this->getPageImport($name, $dotModule);

        $content = file_get_contents($file);

        $importsEnd = strrpos($content, '\';') + 3;

        $edit = substr($content, 0, $importsEnd);
        $edit .= $this->clientTemplates->importFrom([ucfirst($name)], $importPath);
        $edit .= substr($content, $importsEnd);

        $content = $edit;
        $routesEnd = strrpos($content, ']');

        $edit = substr($content, 0, $routesEnd);
        $edit .= $this->tab.'{path: \''.camelCaseToDash($name).'\', component: '.ucfirst($name).'},'.PHP_EOL;
        $edit .= substr($content, $routesEnd);

        file_put_contents($file, $edit);
    }

    public function addComponentDeclaration(string $name, string $dotModule = '')
    {
        if ($dotModule == '')
        {
            $dotModule = $this->config['selectedModule'];
        }

        $file = $this->getModuleFile($dotModule).'.declarations.ts';

        $importPath = $this->getComponentImport($name, $dotModule);

        $content = file_get_contents($file);

        $importsEnd = strrpos($content, '\';') + 3;

        $edit = substr($content, 0, $importsEnd);
        $edit .= $this->clientTemplates->importFrom([ucfirst($name)], $importPath);
        $edit .= substr($content, $importsEnd);

        $content = $edit;
        $declarationsEnd = strrpos($content, ']');

        $edit = substr($content, 0, $declarationsEnd);
        $edit .= $this->tab.ucfirst($name).','.PHP_EOL;
        $edit .= substr($content, $declarationsEnd);

        file_put_contents($file, $edit);
    }

    public function registerReducer(string $name)
    {
        $reducerName = $name.'Reducer';
        $state = ucfirst($name).'State';

        $content = file_get_contents($this->getReducerIndexFile());

        $importsEnd = strrpos($content, '\';') + 3;

        $edit = substr($content, 0, $importsEnd);
        $edit .= $this->clientTemplates->importFrom([$reducerName, $state], './'.$name.'.reducer');
        $edit .= substr($content, $importsEnd);

        $content = $edit;
        $appStateBegin = strrpos($content, 'AppState {') + 11;

        $edit = substr($content, 0, $appStateBegin);
        $edit .= $this->tab.$state.','.PHP_EOL;
        $edit .= substr($content, $appStateBegin);

        $content = $edit;
        $reducersBegin = strrpos($content, 'reducers = {') + 13;

        $edit = substr($content, 0, $reducersBegin);
        $edit .= $this->tab.$reducerName.','.PHP_EOL;
        $edit .= substr($content, $reducersBegin);

        file_put_contents($this->getReducerIndexFile(), $edit);
    }

    public function registerActions(string $name)
    {
        $className = ucfirst($name).'Actions';

        $content = file_get_contents($this->getActionsIndexFile());

        $importsEnd = strrpos($content, '\';') + 3;

        $edit = substr($content, 0, $importsEnd);
        $edit .= $this->clientTemplates->importFrom([$className], './'.$name.'.actions');
        $edit .= substr($content, $importsEnd);

        $content = $edit;
        $exportBegin = strrpos($content, 'export {') + 9;

        $edit = substr($content, 0, $exportBegin);
        $edit .= $this->tab.$className.','.PHP_EOL;
        $edit .= substr($content, $exportBegin);

        $content = $edit;
        $exportDefaultBegin = strrpos($content, 'default [') + 10;

        $edit = substr($content, 0, $exportDefaultBegin);
        $edit .= $this->tab.$className.','.PHP_EOL;
        $edit .= substr($content, $exportDefaultBegin);

        file_put_contents($this->getActionsIndexFile(), $edit);
    }

    public function addActionToActionsFile(string $type, string $name, array $args)
    {
        $content = file_get_contents($this->getActionsFile($type));

        preg_match('/export class [a-zA-Z0-9]+ {()/', $content, $matches, PREG_OFFSET_CAPTURE);
        $offset = $matches[1][1] + 1;

        $content = substr($content, 0, $offset)
            .$this->clientTemplates->actionBlock($type, $name, $args)
            .substr($content, $offset);

        file_put_contents($this->getActionsFile($type), $content);
    }

    public function addActionToReducer(string $type, string $name)
    {
        $content = file_get_contents($this->getReducerFile($type));

        preg_match('/export const \w+\s*:\s*ActionReducer<\w+>\s*=\s*\([a-zA-Z0-9_=:\-,\s]+\)\s*=>\s*{\s*switch\(action.type\)\s*{()/', $content, $matches, PREG_OFFSET_CAPTURE);
        $offset = $matches[1][1] + 1;

        $content = substr($content, 0, $offset)
            .$this->clientTemplates->actionCase($type, $name)
            .substr($content, $offset);

        $import = $this->clientTemplates->importFrom([ucfirst($type).'Actions'], './../actions/'.$type.'.actions');
        if (!strpos($content, $import))
        {
            preg_match_all('/import[\s{},a-zA-Z0-9_\-]+from[\a-zA-Z0-9_\-@]+;()/', $content, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
            $offset = $matches[sizeof($matches) - 1][1][1] + 1;

            $content = substr($content, 0, $offset).$import.substr($content, $offset);
        }

        file_put_contents($this->getReducerFile($type), $content);
    }

    private function getReducerIndexFile()
    {
        return $this->config['clientAppDir'].'/reducers/index.ts';
    }

    private function getReducerFile(string $name)
    {
        return $this->config['clientAppDir'].'/reducers/'.$name.'.reducer.ts';
    }

    private function getActionsIndexFile()
    {
        return $this->config['clientAppDir'].'/actions/index.ts';
    }

    private function getActionsFile(string $type)
    {
        return $this->config['clientAppDir'].'/actions/'.$type.'.actions.ts';
    }

    private function getModuleFile(string $dotModule)
    {
        if ($dotModule == 'app')
        {
            return $this->config['clientAppDir'].'/app';
        }

        return $this->config['clientAppDir'].'/modules/'.str_replace('.', '/', $dotModule);
    }

    private function getPageImport(string $name, string $dotModule)
    {
        $path = './';
        if ($dotModule != 'app')
        {
            $path .= '../';
            for ($i = 0; $i < sizeof(explode('.', $dotModule)); $i++)
            {
                $path .= '../';
            }
        }

        $path .= 'pages/';

        if ($dotModule != 'app')
        {
            foreach (explode('.', $dotModule) as $moduleName)
            {
                $path .= $moduleName.'/';
            }
        }

        $path .= $name.'/'.ucfirst($name);

        return $path;
    }

    private function getComponentImport(string $name, string $dotModule)
    {
        $path = './';
        if ($dotModule != 'app')
        {
            $path .= '../';
            for ($i = 0; $i < sizeof(explode('.', $dotModule)); $i++)
            {
                $path .= '../';
            }
        }

        $path .= 'components/'.$name.'/'.ucfirst($name);

        return $path;
    }
}