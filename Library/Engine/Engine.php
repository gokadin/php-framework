<?php

namespace Library\Engine;

use Library\DataMapper\Database\QueryBuilder;
use Library\Engine\Queries\CreateQuery;
use Library\Engine\Queries\EngineQueryExecutor;
use Library\Engine\Queries\FetchQuery;
use Library\Http\Response;
use Library\DataMapper\Collection\EntityCollection;
use Library\DataMapper\DataMapper;
use Library\Container\Container;
use \ReflectionClass;
use \Exception;

class Engine
{
    private const FETCH_KEY = 'FETCH';
    private const CREATE_KEY = 'CREATE';
    private const UPDATE_KEY = 'UPDATE';
    private const DELETE_KEY = 'DELETE';

    /**
     * @var DataMapper
     */
    private $dm;

    /**
     * @var array
     */
    private $commands = [];

    /**
     * @var EngineQueryExecutor
     */
    private $queryExecutor;

    /**
     * @var string
     */
    private $modelsNamespace;

    /**
     * Engine constructor.
     *
     * @param array $schema
     * @param DataMapper $dm
     * @param Container $container
     * @param array $config
     */
    public function __construct(array $schema, DataMapper $dm, Container $container, array $config)
    {
        $this->dm = $dm;

        $this->readConfig($config);

        $this->queryExecutor = new EngineQueryExecutor($schema, $dm, $container, $config);
    }

    /**
     * @param array $config
     */
    private function readConfig(array $config)
    {
        $this->modelsNamespace = str_replace('/', '\\', $config['modelsPath']).'\\';
    }

    /**
     * @param $type
     * @param array $fields
     * @return FetchQuery
     */
    public function fetch($type, array $fields): FetchQuery
    {
        $queryBuilder = $this->getTypeQueryBuilder($type);
        $this->commands[] = [
            'type' => $type,
            'entityClassName' => $this->getEntityClassName($type),
            'queryBuilder' => $queryBuilder,
            'action' => self::FETCH_KEY,
            'fields' => $fields
        ];

        return new FetchQuery($queryBuilder);
    }


    public function create($type, array $data, array $fields = []): CreateQuery
    {
        $queryBuilder = $this->getTypeQueryBuilder($type);
        $this->commands[] = [
            'type' => $type,
            'entityClassName' => $this->getEntityClassName($type),
            'queryBuilder' => $queryBuilder,
            'action' => self::FETCH_KEY,
            'fields' => $fields
        ];

        return new CreateQuery($queryBuilder);
    }

    /**
     * @param $type
     * @param array $data
     * @param array $fields
     */
    public function update($type, array $data, array $fields = [])
    {
        $data['fields'] = $fields;
        $this->commands[] = ['type' => $type, 'action' => self::UPDATE_KEY, 'data' => $data];
    }

    /**
     * @param $type
     * @param array $data
     */
    public function delete($type, array $data)
    {
        $this->commands[] = ['type' => $type, 'action' => self::DELETE_KEY, 'data' => $data];
    }

    /**
     * @param string $type
     * @return QueryBuilder
     */
    private function getTypeQueryBuilder(string $type): QueryBuilder
    {
        $entityClassName = $this->modelsNamespace.ucfirst($type);
        $metadata = $this->dm->getMetadata($entityClassName);
        return $this->dm->queryBuilder()->table($metadata->table());
    }

    /**
     * @param string $type
     * @return string
     */
    private function getEntityClassName(string $type): string
    {
        return $this->modelsNamespace.ucfirst($type);
    }

    /**
     * @return array
     */
    public function run(): array
    {
        try
        {
            return [
                'status' => Response::STATUS_OK,
                'content' => $this->runCommands()
            ];
        }
        catch (Exception $e)
        {
            return [
                'status' => Response::STATUS_BAD_REQUEST,
                'content' => $e->getMessage()
            ];
        }
        finally
        {
            $this->commands = [];
        }
    }

    /**
     * @return array
     */
    private function runCommands(): array
    {
        $results = [];
        foreach ($this->commands as $command)
        {
            $results[$command['type']] = $this->queryExecutor->execute($command);
        }

        return $results;
    }

    private function processUpdate(array $action)
    {
        $results = [];
        foreach ($action as $entityName => $updateObject)
        {
            $entityClassName = $this->modelsNamespace.ucfirst($entityName);
            $metadata = $this->dm->getMetadata($entityClassName);
            $queryBuilder = $this->dm->queryBuilder()->table($metadata->table());

            if (!isset($updateObject['conditions']))
            {
                throw new EngineException('Cannot process update request without conditions.');
            }

            foreach ($updateObject['conditions'] as $condition)
            {
                if (is_string($condition) && strtoupper($condition) == 'ALL')
                {
                    break;
                }

                if (sizeof($condition) == 3)
                {
                    $queryBuilder->where($condition[0], $condition[1], $condition[2]);
                    continue;
                }

                if (strtoupper($condition[0]) != 'OR')
                {
                    throw new EngineException('Invalid condition received.');
                }

                $queryBuilder->orWhere($condition[1], $condition[2], $condition[3]);
            }
            $entities = $this->dm->processQueryResults($entityClassName, $queryBuilder->select())->toArray();

            $controller = $this->getController($entityName);

            foreach ($entities as $entity)
            {
                foreach ($updateObject['values'] as $field => $value)
                {
                    $setter = 'set'.ucfirst($field);
                    $entity->$setter($value);
                }

                if (isset($updateObject['fields']))
                {
                    $entityFields = [];
                    foreach ($updateObject['fields'] as $field => $metadata)
                    {
                        $getter = 'get'.ucfirst($field);
                        $entityFields[$metadata['as']] = $entity->$getter();
                    }
                    $results[$entityName][] = $entityFields;
                }

                if (is_null($controller))
                {
                    continue;
                }
                $controllerMethod = 'onUpdate';
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

        return $results;
    }

    private function processDelete(array $action)
    {
        foreach ($action as $entityName => $deleteObjects)
        {
            $entityClassName = $this->modelsNamespace.ucfirst($entityName);
            $metadata = $this->dm->getMetadata($entityClassName);
            $queryBuilder = $this->dm->queryBuilder()->table($metadata->table());

            if (!isset($deleteObjects['conditions']))
            {
                throw new EngineException('Cannot process delete request without conditions.');
            }

            foreach ($deleteObjects['conditions'] as $condition)
            {
                if (is_string($condition) && strtoupper($condition) == 'ALL')
                {
                    break;
                }

                if (sizeof($condition) == 3)
                {
                    $queryBuilder->where($condition[0], $condition[1], $condition[2]);
                    continue;
                }

                if (strtoupper($condition[0]) != 'OR')
                {
                    throw new EngineException('Invalid condition received.');
                }

                $queryBuilder->orWhere($condition[1], $condition[2], $condition[3]);
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

        return [];
    }

    private function processCreate(array $action)
    {
        $results = [];
        foreach ($action as $entityName => $createObjects)
        {
            $entities = [];
            foreach ($createObjects['values'] as $createData)
            {
                $entities[] = $this->createForEntity($entityName, $createData);
            }

            if (sizeof($createObjects['values']) > 0)
            {
                $this->dm->flush();

                $controller = $this->getController($entityName);

                foreach ($entities as $entity)
                {
                    if (isset($createObjects['fields']))
                    {
                        $entityFields = [];
                        foreach ($createObjects['fields'] as $field => $metadata)
                        {
                            $getter = 'get'.ucfirst($field);
                            $entityFields[$metadata['as']] = $entity->$getter();
                        }
                        $results[$entityName][] = $entityFields;
                    }

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

    private function createForEntity(string $entityName, array $createData)
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