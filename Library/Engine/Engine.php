<?php

namespace Library\Engine;

use Library\Http\Response;
use Library\DataMapper\Collection\EntityCollection;
use Library\DataMapper\DataMapper;
use Library\Container\Container;
use Library\Engine\Events\CreateEvent;
use Library\Engine\Events\DeleteEvent;
use \ReflectionClass;
use \RuntimeException;
use \ReflectionMethod;

class Engine
{
    const CONTROLLER_NAMESPACE = '\\App\\Http\\Controllers';

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

    public function __construct(array $schema, DataMapper $dm, Container $container)
    {
        $this->schema = $schema;
        $this->dm = $dm;
        $this->container = $container;
    }

    public function run(array $data)
    {
        return [
            'status' => Response::STATUS_OK,
            'content' => $this->processData($data)
        ];
    }

    private function processData(array $data)
    {
        $result = [];

        foreach ($data as $type => $action)
        {
            $result[$type] = $this->processAction($type, $action);

            if (sizeof($data) == 1)
            {
                return $result[$type];
            }
        }

        return $result;
    }

    private function processAction(string $type, array $action)
    {
        switch ($type)
        {
        case 'fetch':
            return $this->processFetch($action);
        case 'create':
            return $this->processCreate($action);
        case 'delete':
            return $this->processDelete($action);
        default:
            return 'not implemented';
        }
    }

    private function processFetch(array $action)
    {
        $data = [];

        foreach ($action as $entityName => $fetchObject)
        {
            $data[$entityName] = $this->fetchEntities($entityName, $fetchObject);
        }

        return $data;
    }

    private function fetchEntities(string $entityName, $fetchData)
    {
        $entityClassName = '\\App\\Domain\\Models\\'.ucfirst($entityName);
        $metadata = $this->dm->getMetadata($entityClassName);
        $queryBuilder = $this->dm->queryBuilder()->table($metadata->table());

        if (isset($fetchData['sort']))
        {
            foreach ($fetchData['sort'] as $field => $direction)
            {
                $queryBuilder->sortBy($field, strtoupper($direction) == 'ASC');
            }
        }

        if (isset($fetchData['conditions']))
        {
            foreach ($fetchData['conditions'] as $condition)
            {
                $queryBuilder->where($condition[0], $condition[1], $condition[2]);
            }
        }

        $entities = $this->dm->processQueryResults($entityClassName, $queryBuilder->select());

        return $this->buildEntities($entities, $fetchData);
    }

    private function buildEntities($entities, array $fetchData)
    {
        $results = [];

        foreach ($entities as $entity)
        {
            $results[] = $this->buildEntityFields($entity, $fetchData['fields']);
        }

        return $results;
    }
     
    private function buildEntityFields($entity, array $fields)
    {
        $result = [];

        foreach ($fields as $field => $metadata)
        {
            $getter = 'get'.ucfirst($field);
            $alias = isset($metadata['as']) ? $metadata['as'] : $field;

            if (array_key_exists($field, $this->schema))
            {
                $relation = $entity->$getter();
                $result[$alias] = $this->buildEntities($relation->toArray(), $metadata);

                continue;
            }

            $result[$alias] = $entity->$getter();
        }
        
        return $result;
    }

    private function processDelete(array $action)
    {
        foreach ($action as $entityName => $deleteObjects)
        {
            $entityClassName = '\\App\\Domain\\Models\\'.ucfirst($entityName);
            $metadata = $this->dm->getMetadata($entityClassName);
            $queryBuilder = $this->dm->queryBuilder()->table($metadata->table());

            if (isset($deleteObjects['conditions']))
            {
                foreach ($deleteObjects['conditions'] as $condition)
                {
                    $queryBuilder->where($condition[0], $condition[1], $condition[2]);
                }
            }
            $entities = $this->dm->processQueryResults($entityClassName, $queryBuilder->select())->toArray();

            $controller = $this->getController($entityName);

            foreach ($entities as $entity)
            {
                $this->dm->delete($entity);

                if (is_null($controller))
                {
                    continue;
                }
                $controllerMethod = 'onDelete';
                if (method_exists($controller, $controllerMethod))
                {
                    $parameters = $this->getResolvedParameters($controller, $controllerMethod);
                    call_user_func_array([$controller, $controllerMethod], array_merge([new DeleteEvent($entity)], $parameters));
                }
            }

            if (sizeof($entities) > 0)
            {
                $this->dm->flush();
            }
        }
    }

    private function processCreate(array $action)
    {
        foreach ($action as $entityName => $createObjects)
        {
            $entities = [];
            foreach ($createObjects['values'] as $createData)
            {
                $entities[] = $this->createForEntity($entityName, $createData, $createObjects['fields']);
            }

            if (sizeof($createObjects['values']) > 0)
            {
                $this->dm->flush();

                $controller = $this->getController($entityName);

                foreach ($entities as $entity)
                {
                    $entityFields = [];
                    foreach ($createObjects['fields'] as $field => $metadata)
                    {
                        $getter = 'get'.ucfirst($field);
                        $entityFields[$metadata['as']] = $entity->$getter();
                    }
                    $results[$entityName][] = $entityFields;

                    if (is_null($controller))
                    {
                        continue;
                    }

                    $controllerMethod = 'onCreate';
                    if (method_exists($controller, $controllerMethod))
                    {
                        $parameters = $this->getResolvedParameters($controller, $controllerMethod);
                        call_user_func_array([$controller, $controllerMethod], array_merge([new CreateEvent($entity)], $parameters));
                    }
                }
            }
        }

        return $results;
    }

    private function createForEntity(string $entityName, array $createData, array $fields)
    {
        $entityClassName = '\\App\\Domain\\Models\\'.ucfirst($entityName);
        $reflector = new ReflectionClass($entityClassName);
        $entity = $reflector->newInstanceWithoutConstructor();

        foreach ($createData as $fieldName => $value)
        {
            if (strlen($fieldName) > 2 && substr($fieldName, strlen($fieldName) - 2) == 'Id')
            {
                $schemaTypeName = substr($fieldName, 0, -2);
                $setter = 'set'.ucfirst($schemaTypeName);
                $parentClass = '\\App\\Domain\\Models\\'.ucfirst($schemaTypeName);
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

    private function getController(string $entityName)
    {
        $controllerClassName = self::CONTROLLER_NAMESPACE.'\\'.ucfirst($entityName).'Controller';
        $controller = null;
        if (class_exists($controllerClassName))
        {
            $controller = $this->container->resolve($controllerClassName);
        }

        return $controller;
    }

    private function getResolvedParameters($controller, string $controllerMethod)
    {
        $resolvedParameters = [];
        $r = new ReflectionMethod($controller, $controllerMethod);

        foreach ($r->getParameters() as $parameter)
        {
            if ($parameter->getName() == 'event')
            {
                continue;
            }

            $class = $parameter->getClass();
            if (!is_null($class))
            {
                $resolvedParameters[] = $this->container->resolve($class->getName());
                continue;
            }

            if ($parameter->isOptional())
            {
                continue;
            }

            throw new RuntimeException('Could not resolve parameter '.$parameter->getName().' for controller method '.$controllerMethod);
            return [];
        }

        return $resolvedParameters;
    }
}