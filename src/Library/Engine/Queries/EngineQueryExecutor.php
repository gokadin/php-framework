<?php

namespace Library\Engine\Queries;

use Library\Container\Container;
use Library\DataMapper\Collection\EntityCollection;
use Library\DataMapper\DataMapper;
use Library\Engine\Engine;
use Library\Engine\EngineException;
use ReflectionClass;
use ReflectionException;
use ReflectionObject;

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
     * @var string
     */
    private $modelsNamespace;

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
        $this->modelsNamespace = str_replace('/', '\\', $config['modelsPath']).'\\';
    }

    /**
     * @param array $command
     * @return array
     */
    public function execute(array $command): array
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
            case Engine::DELETE_KEY:
                $result = $this->executeDelete($command);
                break;
            case Engine::UPDATE_KEY:
                $result = $this->executeUpdate($command);
                break;
        }

        $this->executeHook($command, $result);

        return $result;
    }

    /**
     * @param array $command
     * @return array
     */
    private function executeFetch(array $command): array
    {
        $entities = $this->dm->processQueryResults($command['entityClassName'], $command['queryBuilder']->select());
        return $this->buildFieldsFromEntities($entities, $command['fields']);
    }

    /**
     * @param array $command
     * @return array
     */
    private function executeCreate(array $command): array
    {
        if (sizeof($command['data']) == 0)
        {
            return [];
        }

        return isset($command['data'][0]) && is_array($command['data'][0])
            ? $this->executeMultipleCreate($command)
            : $this->executeSingleCreate($command);
    }

    /**
     * @param array $command
     * @return array
     */
    private function executeMultipleCreate(array $command): array
    {
        $entities = [];
        foreach ($command['data'] as $values)
        {
            $entity = $this->buildEntityFromData($command['type'], $values);
            $entities[] = $entity;
        }

        $this->dm->flush();

        return sizeof($command['fields']) > 0
            ? $this->buildFieldsFromEntities($entities, $command['fields'])
            : [];
    }

    /**
     * @param array $command
     * @return array
     */
    private function executeSingleCreate(array $command): array
    {
        $entity = $this->buildEntityFromData($command['type'], $command['data']);
        $this->dm->flush();

        return sizeof($command['fields']) > 0
            ? [$this->buildFieldsFromEntity($entity, $command['fields'])]
            : [];
    }

    /**
     * @param array $command
     * @return array
     */
    private function executeDelete(array $command): array
    {
        $entities = $this->dm->processQueryResults($command['entityClassName'], $command['queryBuilder']->select())->toArray();
        foreach ($entities as $entity)
        {
            $this->dm->delete($entity);
        }

        if (sizeof($entities) > 0)
        {
            $this->dm->flush();
        }

        return [];
    }

    /**
     * @param array $command
     * @return array
     */
    private function executeUpdate(array $command): array
    {
        $entities = $this->dm->processQueryResults($command['entityClassName'], $command['queryBuilder']->select())->toArray();
        foreach ($entities as $entity)
        {
            foreach ($command['data'] as $field => $value)
            {
                $setter = 'set'.ucfirst($field);
                $entity->$setter($value);
            }
        }

        if (sizeof($entities) > 0)
        {
            $this->dm->flush();
        }

        return $this->buildFieldsFromEntities($entities, $command['fields']);
    }

    /**
     * @param array $command
     * @param array $result
     */
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
     * @param $fields
     * @return array
     */
    private function buildFieldsFromEntities($entities, $fields): array
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
     * @param $fields
     * @return array
     */
    private function buildFieldsFromEntity($entity, $fields): array
    {
        if ($fields == '*')
        {
            $fields = $this->findAllFieldsOfEntity($entity);
        }

        $result = [];
        foreach ($fields as $field => $metadata)
        {
            if (is_string($metadata))
            {
                $field = $metadata;
            }

            $getter = 'get'.ucfirst($field);
            $alias = $field;
            if (is_array($metadata))
            {
                if (isset($metadata['as']))
                {
                    $alias = $metadata['as'];
                }
            }

            if (is_array($metadata) && sizeof($metadata) > 0 && isset($metadata[0]))
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
     * @param $entity
     * @return array
     */
    private function findAllFieldsOfEntity($entity): array
    {
        $fields = [];
        $r = new ReflectionObject($entity);

        foreach ($r->getMethods() as $method)
        {
            if ($method->isPublic() && !$method->isConstructor() && substr($method->name, 0, 3) == 'get')
            {
                $fieldName = substr($method->name, 3);
                $fields[$fieldName] = ['as' => $fieldName];
            }
        }

        return $fields;
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

    /**
     * @param string $type
     * @param array $createData
     * @return object
     * @throws EngineException
     * @throws ReflectionException
     */
    private function buildEntityFromData(string $type, array $createData)
    {
        $entityClassName = $this->modelsNamespace.ucfirst($type);
        $reflector = new ReflectionClass($entityClassName);
        $entity = $reflector->newInstanceWithoutConstructor();
        $this->dm->persist($entity);

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

            if (is_array($value))
            {
                $collection = new EntityCollection();
                foreach ($value as $relData)
                {
                    $relType = $this->schema[$type][$fieldName]['hasMany'];
                    $relEntity = $this->buildEntityFromData($relType, $relData);
                    $parentSetter = 'set'.ucfirst($reflector->getShortName());
                    $relEntity->$parentSetter($entity);
                    $this->dm->persist($relEntity);
                    $collection->add($relEntity);
                }

                $value = $collection;
            }

            $setter = 'set'.ucfirst($fieldName);
            $entity->$setter($value);
        }

        return $entity;
    }
}