<?php
namespace Library\Pyramid\Generators\Client\Reducer;

use Library\Pyramid\Generators\Generator;

class ReducerGenerator extends Generator
{
    public function generate(string $name)
    {
        $this->createReducer($name);
        $this->createActions($name);
        $this->configWriter->addReducer($name);
        $this->configWriter->selectReducer($name);
        $this->editor->registerReducer($name);
        $this->editor->registerActions($name);
    }

    private function createReducer(string $name)
    {
        $reducerFile = $this->getReducerFile($name);

        $content = $this->templates->importFrom(['ActionReducer', 'Action'], '@ngrx/store').PHP_EOL;
        $content .= $this->templates->exportInterface(ucfirst($name).'State', []).PHP_EOL;
        $content .= $this->templates->exportConstVariable('initialState', []).PHP_EOL;
        $content .= $this->templates->exportMainReducerFunction($name);

        $this->createFile($reducerFile, $content);
    }

    private function createActions(string $name)
    {
        $actionsFile = $this->getActionsFile($name);

        $content = $this->templates->importFrom(['Injectable'], '@angular/core');
        $content .= $this->templates->importFrom(['Action'], '@ngrx/store').PHP_EOL;
        $content .= $this->templates->injectableAnnotation();
        $content .= 'export class '.ucfirst($name).'Actions {'.PHP_EOL;
        $content .= '}'.PHP_EOL;

        $this->createFile($actionsFile, $content);
    }
}