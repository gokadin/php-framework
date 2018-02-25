<?php

namespace Library\Pyramid\Templates\Client;

class ClientTemplates
{
    private $tab = '    ';

    public function importFrom(array $items, string $package)
    {
        return 'import {'.implode(',', $items).'} from \''.$package.'\';'.PHP_EOL;
    }

    public function injectableAnnotation()
    {
        return '@Injectable()'.PHP_EOL;
    }

    public function emptyClass($name)
    {
        $s = 'export class '.ucfirst($name).' {'.PHP_EOL.PHP_EOL;
        $s .= '}'.PHP_EOL;

        return $s;
    }

    public function componentDeclaration($name)
    {
        $dashName = camelCaseToDash($name);

        $s = '@Component({'.PHP_EOL;
        $s .= $this->tab.'selector: \''.$dashName.'\','.PHP_EOL;
        $s .= $this->tab.'templateUrl: \'./'.$name.'.html\','.PHP_EOL;
        $s .= $this->tab.'styleUrls: [\'./'.$name.'.css\']'.PHP_EOL;
        $s .= '})'.PHP_EOL;

        return $s;
    }

    public function emptyScss($name)
    {
        $s = '.'.camelCaseToDash($name).' {'.PHP_EOL.PHP_EOL;
        $s .= '}'.PHP_EOL;

        return $s;
    }

    public function emptyHtml($name)
    {
        $s = '<div class="'.camelCaseToDash($name).'">'.PHP_EOL.PHP_EOL;
        $s .= '</div>'.PHP_EOL;

        return $s;
    }

    public function exportInterface(string $name, array $values)
    {
        $s = 'export interface '.$name.' {'.PHP_EOL;
        foreach ($values as $key => $value)
        {
            $s .= $this->tab.$key.':'.$value.','.PHP_EOL;
        }
        $s .= '}'.PHP_EOL;

        return $s;
    }

    public function exportConstVariable(string $name, array $values)
    {
        $s = 'export const '.$name.' = {'.PHP_EOL;
        foreach ($values as $key => $value)
        {
            $s .= $this->tab.$key.':'.$value.','.PHP_EOL;
        }
        $s .= '};'.PHP_EOL;

        return $s;
    }

    public function exportMainReducerFunction(string $name)
    {
        $s = 'export const '.$name.'Reducer: ActionReducer<'.ucfirst($name).'State> = (state: '.ucfirst($name).'State = initialState, action: Action) => {'.PHP_EOL;
        $s .= $this->switchAction();

        return $s;
    }

    public function actionBlock(string $type, string $name, array $args)
    {
        $fullActionName = strtoupper(camelCaseToSnake($type)).'_'.strtoupper(camelCaseToSnake($name));

        $s = $this->tab.'static '.$fullActionName.' = \''.$fullActionName.'\';'.PHP_EOL;
        $s .= $this->tab.$name.'('.implode(', ', $args).'): Action {'.PHP_EOL;
        $s .= $this->tab.$this->tab.'return {'.PHP_EOL;
        $s .= $this->tab.$this->tab.$this->tab.'type: '.ucfirst($type).'Actions.'.$fullActionName.','.PHP_EOL;
        if (sizeof($args) > 0)
        {
            $s .= $this->tab.$this->tab.$this->tab.'payload: {'.PHP_EOL;
            foreach ($args as $arg)
            {
                $s .= $this->tab.$this->tab.$this->tab.$this->tab.$arg.','.PHP_EOL;
            }
            $s .= $this->tab.$this->tab.$this->tab.'}'.PHP_EOL;
        }
        $s .= $this->tab.$this->tab.'}'.PHP_EOL;
        $s .= $this->tab.'};'.PHP_EOL.PHP_EOL;

        return $s;
    }

    public function actionCase(string $type, string $name)
    {
        $actionClass = ucfirst($type).'Actions';
        $actionName = $fullActionName = strtoupper(camelCaseToSnake($type)).'_'.strtoupper(camelCaseToSnake($name));

        $s = $this->tab.$this->tab.'case '.$actionClass.'.'.$actionName.':'.PHP_EOL;
        $s .= $this->tab.$this->tab.$this->tab.'return state;'.PHP_EOL;

        return $s;
    }

    private function switchAction()
    {
        $s = $this->tab.'switch(action.type) {'.PHP_EOL;
        $s .= $this->tab.$this->tab.'default:'.PHP_EOL;
        $s .= $this->tab.$this->tab.$this->tab.'return state;'.PHP_EOL;
        $s .= $this->tab.'}'.PHP_EOL;
        $s .= '}'.PHP_EOL;

        return $s;
    }
}