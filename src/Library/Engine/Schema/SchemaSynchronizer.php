<?php

namespace Library\Engine\Schema;

use Library\Utils\StringUtils;

class SchemaSynchronizer
{
    /**
     * @var string
     */
    private $basePath;

    /**
     * @var string
     */
    private $modelsNamespace;

    /**
     * @var string
     */
    private $modelsDirectoryPath;

    /**
     * @var string
     */
    private $controllersNamespace;

    /**
     * @var string
     */
    private $controllersDirectoryPath;

    /**
     * @var string
     */
    private $dataMapperConfigFile;

    /**
     * @var ModelGenerator
     */
    private $modelGenerator;

    /**
     * SchemaSynchronizer constructor.
     *
     * @param string $basePath
     * @param array $engineConfig
     * @param string $dataMapperConfigFile
     */
    public function __construct(string $basePath, array $engineConfig, string $dataMapperConfigFile)
    {
        $this->basePath = $basePath;
        $this->dataMapperConfigFile = $dataMapperConfigFile;

        $this->readConfig($engineConfig);

        $this->modelGenerator = new ModelGenerator($this->modelsNamespace);
    }

    /**
     * @param array $config
     */
    private function readConfig(array $config): void
    {
        $this->modelsNamespace = str_replace('/', '\\', $config['modelsPath']);
        $this->modelsDirectoryPath = $this->basePath.'/'.$config['modelsPath'];

        $this->controllersNamespace = str_replace('/', '\\', $config['controllersPath']);
        $this->controllersDirectoryPath = $this->basePath.'/'.$config['controllersPath'];
    }

    private function createMissingDirectories()
    {
        if (!file_exists($this->modelsDirectoryPath))
        {
            mkdir($this->modelsDirectoryPath, 0777, true);
        }

        if (!file_exists($this->controllersDirectoryPath))
        {
            mkdir($this->controllersDirectoryPath, 0777, true);
        }
    }

    /**
     * @param array $schema
     * @param array $previousSchema
     * @return array
     */
    public function synchronize(array $schema, array $previousSchema): array
    {
        $this->createMissingDirectories();

        try
        {
            foreach (array_diff_key($schema, $previousSchema) as $typeName => $fields)
            {
                $this->addType($typeName, $fields);
            }

            foreach (array_diff_key($previousSchema, $schema) as $typeName => $fields)
            {
                $this->removeType($typeName);
            }

            foreach (array_intersect_key($schema, $previousSchema) as $typeName => $fields)
            {
                $this->synchronizeType($typeName, $fields, $previousSchema);
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

    private function synchronizeType(string $typeName, array $fields, array $previousSchema)
    {
        if ($fields != $previousSchema[$typeName])
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
        $file = $this->modelsDirectoryPath.'/'.ucfirst($typeName).'.php';
        $content = $this->modelGenerator->generate($typeName, $fields);
        file_put_contents($file, $content);
    }

    private function removeModel(string $typeName)
    {
        $file = $this->modelsDirectoryPath.'/'.ucfirst($typeName).'.php';
        if (file_exists($file))
        {
            unlink($file);
        }
    }

    private function generateController(string $typeName)
    {
        $file = $this->controllersDirectoryPath.'/'.ucfirst($typeName).'Controller.php';
        if (file_exists($file))
        {
            return;
        }

        $str = '<?php'.PHP_EOL.PHP_EOL;
        $str .= 'namespace '.$this->controllersNamespace.';'.PHP_EOL.PHP_EOL;
        $str .= 'use Library\Engine\EngineController;'.PHP_EOL.PHP_EOL;
        $str .= 'class '.ucfirst($typeName).'Controller extends EngineController'.PHP_EOL;
        $str .= '{'.PHP_EOL.PHP_EOL;
        $str .= '}'.PHP_EOL;

        file_put_contents($file, $str);
    }

    private function removeController(string $typeName)
    {
        $file = $this->controllersDirectoryPath.'/'.ucfirst($typeName).'Controller.php';
        if (file_exists($file))
        {
            unlink($file);
        }
    }

    private function addToDataMapperConfig(string $typeName)
    {
        $configContent = file_get_contents($this->dataMapperConfigFile);

        $str = '    '.$this->modelsNamespace.'\\'.ucfirst($typeName).'::class,'.PHP_EOL.'    ';

        if (strpos($configContent, $this->modelsNamespace.'\\'.ucfirst($typeName)) !== false)
        {
            return;
        }

        $classesPos = strpos($configContent, '\'classes\' => [') + 14;
        $offset = strpos($configContent, ']', $classesPos);
        $configContent = substr($configContent, 0, $offset).$str.substr($configContent, $offset);

        file_put_contents($this->dataMapperConfigFile, $configContent);
    }

    private function removeFromDataMapperConfig(string $typeName)
    {
        $configContent = file_get_contents($this->dataMapperConfigFile);

        $str = $this->modelsNamespace.'\\'.ucfirst($typeName).'::class';
        $configContent = StringUtils::removeLinesContainingString($configContent, $str);
        file_put_contents($this->dataMapperConfigFile, $configContent);
    }
}