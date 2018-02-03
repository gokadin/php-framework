<?php

namespace Library\Engine\Schema;

use Library\Utils\StringUtils;

class SchemaSynchronizer
{
    const MODEL_DIR = __DIR__.'/../../../App/Domain/Models';
    const MODEL_NAMESPACE = 'App\\Domain\\Models';
    const DATAMAPPER_CONFIG_FILE = __DIR__.'/../../../Config/Datamapper/datamapper.php';
    const CONTROLLER_DIR = __DIR__.'/../../../App/Http/Controllers';
    const CONTROLLER_NAMESPACE = 'App\Http\Controllers';

    /**
     * @var array
     */
    private $schema;

    /**
     * @var array
     */
    private $previousSchema;

    /**
     * @var ModelGenerator
     */
    private $modelGenerator;

    public function __construct(array $schema, array $previousSchema)
    {
        $this->schema = $schema;
        $this->previousSchema = $previousSchema;

        $this->modelGenerator = new ModelGenerator();
    }

    public function synchronize()
    {
        try
        {
            foreach (array_diff_key($this->schema, $this->previousSchema) as $typeName => $fields)
            {
                $this->addType($typeName, $fields);
            }

            foreach (array_diff_key($this->previousSchema, $this->schema) as $typeName => $fields)
            {
                $this->removeType($typeName);
            }

            foreach (array_intersect_key($this->schema, $this->previousSchema) as $typeName => $fields)
            {
                $this->synchronizeType($typeName, $fields);
            }
        }
        catch (SchemaException $e)
        {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }

        return ['success' => true];
    }

    private function synchronizeType(string $typeName, array $fields)
    {
        if ($fields != $this->previousSchema[$typeName])
        {
            $this->generateModel($typeName, $fields);
        }
    }

    private function addType(string $typeName, array $fields)
    {
        $this->generateModel($typeName, $fields);

        $this->generateController($typeName);

        $this->addToDataMapperConfig($typeName);
    }

    private function removeType(string $typeName)
    {
        $this->removeModel($typeName);

        $this->removeController($typeName);

        $this->removeFromDataMapperConfig($typeName);
    }

    private function generateModel(string $typeName, array $fields)
    {
        $file = self::MODEL_DIR.'/'.ucfirst($typeName).'.php';
        $content = $this->modelGenerator->generate($typeName, $fields);
        file_put_contents($file, $content);
    }

    private function removeModel(string $typeName)
    {
        $file = self::MODEL_DIR.'/'.ucfirst($typeName).'.php';
        if (file_exists($file))
        {
            unlink($file);
        }
    }

    private function generateController(string $typeName)
    {
        $file = self::CONTROLLER_DIR.'/'.ucfirst($typeName).'Controller.php';
        if (file_exists($file))
        {
            return;
        }

        $str = '<?php'.PHP_EOL.PHP_EOL;
        $str .= 'namespace '.self::CONTROLLER_NAMESPACE.';'.PHP_EOL.PHP_EOL;
        $str .= 'use Library\Engine\EngineController;'.PHP_EOL.PHP_EOL;
        $str .= 'class '.ucfirst($typeName).'Controller extends EngineController'.PHP_EOL;
        $str .= '{'.PHP_EOL.PHP_EOL;
        $str .= '}'.PHP_EOL;

        file_put_contents($file, $str);
    }

    private function removeController(string $typeName)
    {
        $file = self::CONTROLLER_DIR.'/'.ucfirst($typeName).'Controller.php';
        if (file_exists($file))
        {
            unlink($file);
        }
    }

    private function addToDataMapperConfig(string $typeName)
    {
        $configContent = file_get_contents(self::DATAMAPPER_CONFIG_FILE);

        $str = '    '.self::MODEL_NAMESPACE.'\\'.ucfirst($typeName).'::class,'.PHP_EOL.'    ';

        if (strpos($configContent, self::MODEL_NAMESPACE.'\\'.ucfirst($typeName)) !== false)
        {
            return;
        }

        $classesPos = strpos($configContent, '\'classes\' => [') + 14;
        $offset = strpos($configContent, ']', $classesPos);
        $configContent = substr($configContent, 0, $offset).$str.substr($configContent, $offset);

        file_put_contents(self::DATAMAPPER_CONFIG_FILE, $configContent);
    }

    private function removeFromDataMapperConfig(string $typeName)
    {
        $configContent = file_get_contents(self::DATAMAPPER_CONFIG_FILE);

        $str = self::MODEL_NAMESPACE.'\\'.ucfirst($typeName).'::class';
        $configContent = StringUtils::removeLinesContainingString($configContent, $str);
        file_put_contents(self::DATAMAPPER_CONFIG_FILE, $configContent);
    }
}