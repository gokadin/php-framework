<?php

namespace Library\DataMapper;

use Library\DataMapper\Collection\EntityCollection;
use Library\DataMapper\Database\QueryBuilder;
use Library\DataMapper\Mapping\Drivers\AnnotationDriver;
use Library\DataMapper\Mapping\Metadata;
use Library\DataMapper\UnitOfWork\UnitOfWork;

class DataMapper
{
    /**
     * @var UnitOfWork
     */
    protected $unitOfWork;

    /**
     * The mapping driver used to parse
     * and build entity metadata.
     *
     * @var MappingDriverInterface
     */
    protected $mappingDriver;

    /**
     * Used to build a queries differently
     * depending on which database driver is configured.
     *
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * Already parsed and loaded metadata from
     * the mapping driver.
     *
     * @var array
     */
    protected $loadedMetadata = [];

    /**
     * Initializes mapping and database drivers.
     *
     * @param array $config
     */
    public function __construct($config)
    {
        $this->unitOfWork = new UnitOfWork($this);

        $this->initializeMappingDriver($config['mappingDriver']);

        $this->queryBuilder = new QueryBuilder($config);
    }

    /**
     * Initializes the mapping driver.
     *
     * @param $driverName
     */
    protected function initializeMappingDriver($driverName)
    {
        switch ($driverName)
        {
            default:
                $this->mappingDriver = new AnnotationDriver();
                break;
        }
    }

    public function unitOfWork()
    {
        return $this->unitOfWork;
    }

    /**
     * Finds the object by id and fully loads it
     * in the unit of work unless already there.
     *
     * @param $class
     * @param $id
     * @return mixed
     */
    public function find($class, $id)
    {
        return $this->unitOfWork->find($class, $id);
    }

    /**
     * Finds an object by id and fully loads it.
     * Throws an exception if not found.
     *
     * @param $class
     * @param $id
     * @return mixed
     * @throws DataMapperException
     */
    public function findOrFail($class, $id)
    {
        $entity = $this->find($class, $id);

        if (is_null($entity))
        {
            throw new DataMapperException('DataMapper.findOrFail : Could not find entity of class '.$class.
                ' with id of '.$id);
        }

        return $entity;
    }

    /**
     * Finds all the objects of the given class.
     * Does not consider managed entities.
     *
     * @param $class
     * @return EntityCollection
     */
    public function findAll($class)
    {
        $metadata = $this->getMetadata($class);

        $allData = $this->queryBuilder()->table($metadata->table())->select();

        return new EntityCollection($this->unitOfWork->processFoundData($class, $allData));
    }

    /**
     * Finds entities which correspond to the
     * given array of ids.
     *
     * @param $class
     * @param array $ids
     * @return EntityCollection
     */
    public function findIn($class, array $ids)
    {
        return new EntityCollection($this->unitOfWork->loadMany($class, $ids));
    }

    /**
     * @param $class
     * @param array $conditions
     * @return EntityCollection
     * @throws DataMapperException
     */
    public function findLike($class, array $conditions)
    {
        $queryBuilder = $this->_findBy($class, $conditions, 'LIKE');

        return new EntityCollection($this->unitOfWork->processFoundData($class, $queryBuilder->select()));
    }

    /**
     * Finds entities of a certain class by the
     * given conditions.
     *
     * @param $class
     * @param array $conditions
     * @return EntityCollection
     * @throws DataMapperException
     */
    public function findBy($class, array $conditions)
    {
        $queryBuilder = $this->_findBy($class, $conditions);

        return new EntityCollection($this->unitOfWork->processFoundData($class, $queryBuilder->select()));
    }

    /**
     * Finds the first matching entity from the given conditions.
     *
     * @param $class
     * @param array $conditions
     * @return mixed
     * @throws DataMapperException
     */
    public function findOneBy($class, array $conditions)
    {
        $queryBuilder = $this->_findBy($class, $conditions);

        $data = $queryBuilder->limit(1)->select();

        $result = $this->unitOfWork->processFoundData($class, $data);
        return sizeof($result) == 0 ? null : $result[0];
    }

    /**
     * Get the prepared query builder for performing
     * a find by function.
     *
     * @param $class
     * @param array $conditions
     * @param string $operator
     * @return $this
     * @throws DataMapperException
     */
    private function _findBy($class, array $conditions, string $operator = '=')
    {
        $metadata = $this->getMetadata($class);

        $queryBuilder = $this->queryBuilder->table($metadata->table());
        foreach ($conditions as $condition)
        {
            $column = $metadata->getColumnByPropName($condition[0]);
            if (is_null($column))
            {
                throw new DataMapperException('DataMapper.findBy : Property '.$condition[0].' does not exist.');
            }

            $queryBuilder->where($column->name(), $condition[1], $condition[2]);
        }

        return $queryBuilder;
    }

    /**
     * Marks the object for persistence
     * and adds it to the unit of work if not yet tracked.
     *
     * @param $object
     */
    public function persist($object)
    {
        $metadata = $this->getMetadata(get_class($object));
        $id = $metadata->reflProp($metadata->primaryKey()->name())->getValue($object);

        if (is_null($id))
        {
            $this->unitOfWork->addNew($object);
        }
    }

    /**
     * @param $object
     * @throws DataMapperException
     */
    public function delete($object)
    {
        $this->unitOfWork->addToRemovals($object);
    }

    /**
     * Executes all scheduled work in the unit of work.
     */
    public function flush()
    {
        $this->unitOfWork->commit();
    }

    /**
     * Stops tracking the object in the unit of work.
     *
     * @param $object
     */
    public function detach($object)
    {
        $this->unitOfWork->detach($object);
    }

    /**
     * Stops tracking all objects in the unit of work.
     */
    public function detachAll()
    {
        $this->unitOfWork->detachAll();
    }

    /**
     * Get the query builder.
     *
     * @return QueryBuilder
     */
    public function queryBuilder()
    {
        return $this->queryBuilder;
    }

    public function processQueryResults($class, $results)
    {
        return new EntityCollection($this->unitOfWork->processFoundData($class, $results));
    }

    /**
     * Gets the metadata for the class
     * and loads it if not yet loaded.
     *
     * @param $class
     * @return Metadata
     */
    public function getMetadata($class)
    {
        if (isset($this->loadedMetadata[$class]))
        {
            return $this->loadedMetadata[$class];
        }

        return $this->loadedMetadata[$class] = $this->mappingDriver->getMetadata($class);
    }
}