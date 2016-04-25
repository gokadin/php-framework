<?php namespace Library\Shao;

class ShaoLogic
{
    public function getValidLogicNames()
    {
        return array(
            'if',
            'endif',
            'else',
            'elseif',
            'for',
            'endfor',
            'foreach',
            'endforeach',
            'while',
            'endwhile'
        );
    }

    public function convertToFunctionName($logicName)
    {
        if (!in_array($logicName, self::getValidLogicNames()))
            return false;

        return 'logic'.ucfirst(strtolower($logicName));
    }

    public function logicIf($string)
    {
        return '<?php if ('.$string.') { ?>';
    }

    public function logicEndif()
    {
        return self::closingBracket();
    }

    public function logicElseif($string)
    {
        return '<?php } else if ('.$string.') { ?>';
    }

    public function logicElse()
    {
        return '<?php } else { ?>';
    }

    public function logicFor($string)
    {
        return '<?php for ('.$string.') { ?>';
    }

    public function logicEndfor()
    {
        return self::closingBracket();
    }

    public function logicForeach($string)
    {
        return '<?php foreach ('.$string.') { ?>';
    }

    public function logicEndforeach()
    {
        return self::closingBracket();
    }

    public function logicWhile($string)
    {
        return '<?php while('.$string.') { ?>';
    }

    public function logicEndwhile()
    {
        return self::closingBracket();
    }

    private function closingBracket()
    {
        return '<?php } ?>';
    }
}
