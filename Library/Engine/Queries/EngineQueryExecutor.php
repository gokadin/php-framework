<?php

namespace Library\Engine\Queries;

use Library\Container\Container;
use Library\DataMapper\DataMapper;
use Library\Engine\Engine;

class EngineQueryExecutor
{
    private const HOOK_PREFIX = 'on';

    /**
     * @var array
     */
    private $schema;

    /**
     * @var DataMapper
     */
    private $dm;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var string
     */
    private $controllersNamespace;

    /**
     * EngineQueryExecutor constructor.
     *
     * @param array $schema
     * @param DataMapper $dm
     * @param Container $container
     * @param array $config
     */
    public function __construct(array $schema, DataMapper $dm, Container $container, array $config)
    {
        $this->schema = $schema;
        $this->dm = $dm;
        $this->container = $container;

        $this->readConfig($config);
    }

    /**
     * @param array $config
     */
    private function readConfig(array $config)
    {
        $this->controllersNamespace = str_replace('/', '\\', $config['controllersPath']).'\\';
    }

    public function execute(array $command)
    {
        $result = [];
        switch ($command['action'])
        {
            case Engine::FETCH_KEY:
                $result = $this->executeFetch($command);
                break;
            case Engine::CREATE_KEY:
                $result = $this->executeCreate($command);
                break;
        }

        $this->executeHook($command, $result);

        return $result;
    }

    private function executeFetch(array $command)
    {
        $entities = $this->dm->processQueryResults($command['entityClassName'], $command['queryBuilder']->select());
        return $this->buildEntities($entities, $command['fields']);
    }

    private function executeCreate(array $command)
    {

    }

    private function executeHook(array $command, array $result)
    {
        $controller = $this->getController($command['type']);
        if (is_null($controller))
        {
            return;
        }

        $methodName = self::HOOK_PREFIX.ucfirst(strtolower($command['action']));
        if (!method_exists($controller, $methodName))
        {
            return;
        }

        $params = $this->container->resolveMethodParameters($controller, $methodName);
        call_user_func_array([$controller, $methodName], $params);
    }

    /**
     * @param $entities
     * @param array $fields
     * @return array
     */
    private function buildFieldsFromEntities($entities, array $fields): array
    {
        $results = [];
        foreach ($entities as $entity)
        {
            $results[] = $this->buildFieldsFromEntity($entity, $fields);
        }

        return $results;
    }

    /**
     * @param $entity
     * @param array $fields
     * @return array
     */
    private function buildFieldsFromEntity($entity, array $fields): array
    {
        $result = [];
        foreach ($fields as $field => $metadata)
        {
            $getter = 'get'.ucfirst($field);
            $alias = isset($metadata['as']) ? $metadata['as'] : $field;

            if (array_key_exists($field, $this->schema))
            {
                $relation = $entity->$getter();
                $result[$alias] = $this->buildFieldsFromEntities($relation->toArray(), $metadata);

                continue;
            }

            $result[$alias] = $entity->$getter();
        }

        return $result;
    }

    /**
     * @param string $entityName
     * @return mixed|null|object
     */
    private function getController(string $entityName)
    {
        $controllerClassName = $this->controllersNamespace.ucfirst($entityName).'Controller';
        return class_exists($controllerClassName) ? $this->container->resolve($controllerClassName) : null;
    }

    private function BuildEntityFromData(string $entityName, array $createData)
    {
        $entityClassName = $this->modelsNamespace.ucfirst($entityName);
        $reflector = new ReflectionClass($entityClassName);
        $entity = $reflector->newInstanceWithoutConstructor();

        foreach ($createData as $fieldName => $value)
        {
            if (strlen($fieldName) > 2 && substr($fieldName, strlen($fieldName) - 2) == 'Id')
            {
                $schemaTypeName = substr($fieldName, 0, -2);
                $setter = 'set'.ucfirst($schemaTypeName);
                $parentClass = $this->modelsNamespace.ucfirst($schemaTypeName);
                $parent = $this->dm->find($parentClass, $value);
                if (is_null($parent))
                {
                    throw new EngineException('Could not find parent type '.$schemaTypeName.' having an id of '.$value);
                }
                $entity->$setter($parent);

                continue;
            }

            $setter = 'set'.ucfirst($fieldName);

            if (is_array($value))
            {
                $entity->$setter(new EntityCollection());
                continue;
            }

            $entity->$setter($value);
        }

        $this->dm->persist($entity);

        return $entity;
    }
}