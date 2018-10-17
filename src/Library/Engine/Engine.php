<?php

namespace Library\Engine;

use Library\DataMapper\Database\QueryBuilder;
use Library\Engine\Queries\CreateQuery;
use Library\Engine\Queries\DeleteQuery;
use Library\Engine\Queries\EngineQueryExecutor;
use Library\Engine\Queries\FetchQuery;
use Library\Engine\Queries\UpdateQuery;
use Library\Http\Response;
use Library\DataMapper\DataMapper;
use Library\Container\Container;
use \Exception;

class Engine
{
    const ROUTE_METHOD = 'POST';
    const FETCH_KEY = 'FETCH';
    const CREATE_KEY = 'CREATE';
    const UPDATE_KEY = 'UPDATE';
    const DELETE_KEY = 'DELETE';

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
     * @var string
     */
    private $uri;

    /**
     * @var EngineDataParser
     */
    private $dataParser;

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

        echo 'a';
        $this->readConfig($config);

        $this->queryExecutor = new EngineQueryExecutor($schema, $dm, $container, $config);
        $this->dataParser = new EngineDataParser($this);
    }

    /**
     * @param array $config
     */
    private function readConfig(array $config)
    {
        $this->modelsNamespace = str_replace('/', '\\', $config['modelsPath']).'\\';
        $this->uri = $config['uri'];
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @param array $data
     * @return array
     */
    public function processData(array $data): array
    {
        $this->dataParser->parse($data, $this->s);

        return $this->run();
    }

    /**
     * @param $type
     * @param $fields
     * @return FetchQuery
     */
    public function fetch($type, $fields): FetchQuery
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

    /**
     * @param $type
     * @param array $data
     * @param $fields
     * @return CreateQuery
     */
    public function create($type, array $data, $fields = []): CreateQuery
    {
        $queryBuilder = $this->getTypeQueryBuilder($type);
        $this->commands[] = [
            'type' => $type,
            'entityClassName' => $this->getEntityClassName($type),
            'queryBuilder' => $queryBuilder,
            'action' => self::CREATE_KEY,
            'fields' => $fields,
            'data' => $data
        ];

        return new CreateQuery($queryBuilder);
    }

    /**
     * @param $type
     * @param array $data
     * @param array $fields
     * @return UpdateQuery
     */
    public function update($type, array $data, array $fields = []): UpdateQuery
    {
        $queryBuilder = $this->getTypeQueryBuilder($type);
        $this->commands[] = [
            'type' => $type,
            'entityClassName' => $this->getEntityClassName($type),
            'queryBuilder' => $queryBuilder,
            'action' => self::UPDATE_KEY,
            'fields' => $fields,
            'data' => $data
        ];

        return new UpdateQuery($queryBuilder);
    }

    /**
     * @param $type
     * @return DeleteQuery
     */
    public function delete($type): DeleteQuery
    {
        $queryBuilder = $this->getTypeQueryBuilder($type);
        $this->commands[] = [
            'type' => $type,
            'entityClassName' => $this->getEntityClassName($type),
            'queryBuilder' => $queryBuilder,
            'action' => self::DELETE_KEY
        ];

        return new DeleteQuery($queryBuilder);
    }

    /**
     * @param string $type
     * @return QueryBuilder
     */
    private function getTypeQueryBuilder(string $type): QueryBuilder
    {
        $entityClassName = $this->getEntityClassName($type);
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
}