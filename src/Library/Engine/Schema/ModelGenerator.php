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
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $imports = [];

    /**
     * @var array
     */
    private $ctorLines = [];

    /**
     * @var array
     */
    private $fields = [];

    /**
     * @var array
     */
    private $methods = [];

    /**
     * @var bool
     */
    private $collectionImportAdded;

    /**
     * ModelGenerator constructor.
     * @param string $modelsNamespace
     */
    public function __construct(string $modelsNamespace)
    {
        $this->modelsNamespace = $modelsNamespace;
    }

    private function reset()
    {
        $this->fields = [];
        $this->ctorLines = [];
        $this->imports = [];
        $this->methods = [];
    }

    /**
     * @param string $type
     * @param array $fields
     * @return string
     */
    public function generate(string $type, array $fields): string
    {
        $this->reset();

        $this->type = $type;
        $this->collectionImportAdded = false;

        $str = '<?php'.PHP_EOL.PHP_EOL;
        $str .= 'namespace '.$this->modelsNamespace.';'.PHP_EOL.PHP_EOL;

        $this->generateImports();
        $this->generateFields($fields);

        foreach ($this->imports as $import)
        {
            $str .= $import.PHP_EOL;
        }
        $str .= PHP_EOL;
        $str .= '/** @Entity */'.PHP_EOL;
        $str .= 'class '.ucfirst($this->type).PHP_EOL;
        $str .= '{'.PHP_EOL;
        $str .= '    use DataMapperPrimaryKey, DataMapperTimestamps;'.PHP_EOL;

        foreach ($this->fields as $field)
        {
            $str .= $field.PHP_EOL;
        }

        $str .= $this->generateConstructor();

        $str .= $this->generateGetters($fields);
        $str .= $this->generateSetters($fields);

        $str .= PHP_EOL.'}'.PHP_EOL;

        return $str;
    }

    private function generateImports()
    {
        $this->imports[] = 'use Library\DataMapper\DataMapperPrimaryKey;';
        $this->imports[] = 'use Library\DataMapper\DataMapperTimestamps;';
    }

    private function generateConstructor()
    {
        $str = PHP_EOL;
        $str .= '    public function __construct() {'.PHP_EOL;
        foreach ($this->ctorLines as $line)
        {
            $str .= '        '.$line.PHP_EOL;
        }
        if (sizeof($this->ctorLines) == 0)
        {
            $str .= PHP_EOL;
        }
        $str .= '    }';

        return $str;
    }

    private function generateFields(array $fields)
    {
        foreach ($fields as $name => $attributes)
        {
            if (isset($attributes['type']))
            {
                $this->fields[] = $this->generateScalarField($name, $attributes);
                continue;
            }

            $this->fields[] = $this->generateRelationshipField($name, $attributes);
        }
    }

    private function generateScalarField(string $name, array $attributes)
    {
        $str = PHP_EOL;
        $str .= '    /** @Column(type="'.$attributes['type'].'") */'.PHP_EOL;
        $str .= '    private $'.$name.';';

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
        if ($key == 'hasMany')
        {
            $str .= '", mappedBy="'.$this->type;
            $this->addCollectionImport($name);
        }

        $str .= '") */'.PHP_EOL;
        $str .= '    private $'.$name.';';

        return $str;
    }

    private function addCollectionImport($name)
    {
        $this->ctorLines[] = '$this->'.$name.' = new EntityCollection();';

        if ($this->collectionImportAdded)
        {
            return;
        }

        $this->collectionImportAdded = true;

        $this->imports[] = 'use Library\DataMapper\Collection\EntityCollection;';
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