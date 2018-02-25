<?php

namespace Library\Utils;

class StringUtils
{
    public static function tab($count = 1)
    {
        $str = '';
        for ($i = 0; $i < $count; $i++)
        {
            $str .= '    ';
        }
        return $str;
    }

    public static function camelCaseToDash(string $str)
    {
        $data = preg_split('/(?=[A-Z])/', $str);

        foreach ($data as &$word)
        {
            $word = lcfirst($word);
        }

        return implode('-', $data);
    }

    public static function camelCaseToConstant(string $str)
    {
        $data = preg_split('/(?=[A-Z])/', $str);

        foreach ($data as &$word)
        {
            $word = strtoupper($word);
        }

        return implode('_', $data);
    }

    public static function constantToCamelCase(string $str)
    {
        $parts = explode('_', $str);

        $counter = 0;
        foreach ($parts as &$part)
        {
            $part = strtolower($part);
            if ($counter > 0)
            {
                $part = ucfirst($part);
            }

            $counter++;
        }

        return implode('', $parts);
    }

    public static function removeLinesContainingString(string $content, string $str)
    {
        $lines = explode(PHP_EOL, $content);
        $newLines = [];
        foreach ($lines as $line) {
            if (strpos($line, $str) !== false) {
                continue;
            }

            $newLines[] = $line;
        }

        return implode(PHP_EOL, $newLines);
    }
}