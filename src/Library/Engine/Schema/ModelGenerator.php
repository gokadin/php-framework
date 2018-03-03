<?php

namespace Library\Engine\Schema;

use Library\Engine\EngineException;

class ModelGenerator
{
    /**
     * @var string
     */
    private $modelsNamespace;

    /**
     * ModelGenerator constructor.
     * @param string $modelsNamespace
     */
    public function __construct(string $modelsNamespace)
    {
        $this->modelsNamespace = $modelsNamespace;
    }

    /**
     * @param string $typeName
     * @param array $fields
     * @return string
     */
    public function generate(string $typeName, array $fields): string
    {
        $str = '<?php'.PHP_EOL.PHP_EOL;
        $str .= 'namespace '.$this->modelsNamespace.';'.PHP_EOL.PHP_EOL;
        $str .= 'use Library\DataMapper\DataMapperPrimaryKey;'.PHP_EOL;
        $str .= 'use Library\DataMapper\DataMapperTimestamps;'.PHP_EOL.PHP_EOL;
        $str .= '/** @Entity */'.PHP_EOL;
        $str .= 'class '.ucfirst($typeName).PHP_EOL;
        $str .= '{'.PHP_EOL;
        $str .= '    use DataMapperPrimaryKey, DataMapperTimestamps;'.PHP_EOL;

        $str .= $this->generateFields($fields);

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

    private function generateFields(array $fields)
    {
        $str = '';

        foreach ($fields as $name => $attributes)
        {
            if (isset($attributes['type']))
            {
                $str .= $this->generateScalarField($name, $attributes);
                continue;
            }

            $str .= $this->generateRelationshipField($name, $attributes);
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

    private function generateRelationshipField(string $name, array $attributes)
    {
        $str = PHP_EOL;
        $str .= '    /** @';
        $key = '';
        if (isset($attributes['hasOne']))
        {
            $key = 'hasOne';
        }
        else if (isset($attributes['hasMany']))
        {
            $key = 'hasMany';
        }
        else if (isset($attributes['belongsTo']))
        {
            $key = 'belongsTo';
        }
        else
        {
            throw new EngineException('Invalid schema declaration for field '.$name);
        }

        $str .= ucfirst($key).'(target="'.$this->modelsNamespace.'\\'.ucfirst($attributes[$key]);

        $str .= '") */'.PHP_EOL;
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