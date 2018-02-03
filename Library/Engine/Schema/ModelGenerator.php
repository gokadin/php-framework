<?php

namespace Library\Engine\Schema;

class ModelGenerator
{
    const MODEL_NAMESPACE = 'App\Domain\Models';

    public function generate(string $typeName, array $fields)
    {
        $str = '<?php'.PHP_EOL.PHP_EOL;
        $str .= 'namespace '.self::MODEL_NAMESPACE.';'.PHP_EOL.PHP_EOL;
        $str .= 'use Library\DataMapper\DataMapperPrimaryKey;'.PHP_EOL;
        $str .= 'use Library\DataMapper\DataMapperTimestamps;'.PHP_EOL.PHP_EOL;
        $str .= '/** @Entity */'.PHP_EOL;
        $str .= 'class '.ucfirst($typeName).PHP_EOL;
        $str .= '{'.PHP_EOL;
        $str .= '    use DataMapperPrimaryKey, DataMapperTimestamps;'.PHP_EOL;

        $str .= $this->generateScalarFields($fields);

        $str .= $this->generateConstructor();

        $str .= $this->generateGetters($fields);
        $str .= $this->generateSetters($fields);

        $str .= PHP_EOL.'}'.PHP_EOL;

        return $str;
    }

    private function generateConstructor()
    {
        $str = PHP_EOL;
        $str .= '    public function __construct() {'.PHP_EOL.PHP_EOL;
        $str .= '    }';

        return $str;
    }

    private function generateScalarFields(array $fields)
    {
        $str = '';

        foreach ($fields as $name => $attributes)
        {
            if (isset($attributes['type']))
            {
                $str .= $this->generateScalarField($name, $attributes);
            }
        }

        return $str;
    }

    private function generateScalarField(string $name, array $attributes)
    {
        $str = PHP_EOL;
        $str .= '    /** @Column(type="'.$attributes['type'].'") */'.PHP_EOL;
        $str .= '    private $'.$name.';'.PHP_EOL;

        return $str;
    }

    private function generateGetters(array $fields)
    {
        $str = '';

        foreach ($fields as $name => $attributes)
        {
            $str .= $this->generateGetter($name);
        }

        return $str;
    }

    private function generateGetter(string $name)
    {
        $str = PHP_EOL.PHP_EOL;
        $str .= '    public function get'.ucfirst($name).'() {'.PHP_EOL;
        $str .= '        return $this->'.$name.';'.PHP_EOL;
        $str .= '    }';

        return $str;
    }

    private function generateSetters(array $fields)
    {
        $str = '';

        foreach ($fields as $name => $attributes)
        {
            $str .= $this->generateSetter($name);
        }

        return $str;
    }

    private function generateSetter(string $name)
    {
        $str = PHP_EOL.PHP_EOL;
        $str .= '    public function set'.ucfirst($name).'($value) {'.PHP_EOL;
        $str .= '        $this->'.$name.' = $value;'.PHP_EOL;
        $str .= '    }';

        return $str;
    }
}