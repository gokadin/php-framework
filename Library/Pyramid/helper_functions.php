<?php

function slashToDot(string $str): string
{
    return str_replace('/', '.', $str);
}

function camelCaseToDash(string $str): string
{
    $result = '';

    foreach (str_split($str) as $index => $c)
    {
        if ($index == 0)
        {
            $result .= $c;
            continue;
        }

        if (ctype_upper($c))
        {
            $result .= '-'.strtolower($c);
            continue;
        }

        $result .= $c;
    }

    return $result;
}

function camelCaseToSnake(string $str): string
{
    $result = '';

    foreach (str_split($str) as $index => $c)
    {
        if ($index == 0)
        {
            $result .= $c;
            continue;
        }

        if (ctype_upper($c))
        {
            $result .= '_'.strtolower($c);
            continue;
        }

        $result .= $c;
    }

    return $result;
}

function tab(int $count = 1): string
{
    $tab = '';

    for ($i = 0; $i < $count; $i++)
    {
        $tab .= '    ';
    }

    return $tab;
}