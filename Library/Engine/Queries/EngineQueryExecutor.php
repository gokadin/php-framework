<?php

namespace Library\Engine\Queries;

use Library\Container\Container;
use Library\DataMapper\DataMapper;

class EngineQueryExecutor
{
    private const HOOK_PRE_PREFIX = 'pre';
    private const HOOK_POST_PREFIX = 'post';

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
        $controller = $this->getController($command['type']);

        $this->executeHook($controller, self::HOOK_PRE_PREFIX, $command['action']);

        $entities = $this->dm->processQueryResults($command['entityClassName'], $command['queryBuilder']->select());

        $this->executeHook($controller, self::HOOK_POST_PREFIX, $command['action']);

        return $this->buildEntities($entities, $command['fields']);
    }

    private function executeFetch()
    {

    }

    private function executeCreate()
    {

    }

    private function executeHook($controller, string $prefix, string $action)
    {
        if (is_null($controller))
        {
            return;
        }

        $methodName = $prefix.ucfirst(strtolower($action));
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
    private function buildEntities($entities, array $fields): array
    {
        $results = [];
        foreach ($entities as $entity)
        {
            $results[] = $this->buildEntityFields($entity, $fields);
        }

        return $results;
    }

    /**
     * @param $entity
     * @param array $fields
     * @return array
     */
    private function buildEntityFields($entity, array $fields): array
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

    /**
     * @param string $entityName
     * @return mixed|null|object
     */
    private function getController(string $entityName)
    {
        $controllerClassName = $this->controllersNamespace.ucfirst($entityName).'Controller';
        return class_exists($controllerClassName) ? $this->container->resolve($controllerClassName) : null;
    }
}