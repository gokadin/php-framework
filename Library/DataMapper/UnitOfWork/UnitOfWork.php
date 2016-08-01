<?php

namespace Library\DataMapper\UnitOfWork;

use Carbon\Carbon;
use Library\DataMapper\Collection\EntityCollection;
use Library\DataMapper\Collection\PersistentCollection;
use Library\DataMapper\DataMapper;
use Exception;
use Library\DataMapper\DataMapperException;
use Library\DataMapper\Mapping\Association;
use Library\DataMapper\Mapping\Metadata;
use Library\DataMapper\Observable;
use Library\DataMapper\Observer;
use Library\DataMapper\Persisters\BatchEntityPersister;
use Library\DataMapper\Persisters\EntityPersister;
use Library\DataMapper\Persisters\SingleEntityPersister;
use Library\DataMapper\Proxy\ProxyEntity;

/**
 * Keeps track of all entities known to data mapper
 * and their states.
 */
final class UnitOfWork implements Observable
{
    /**
     * Lets the collections know that the unit of work
     * finished commiting changes.
     */
    const EVENT_COMMITED = 'EVENT_COMMITED';

    /**
     * Entity does not exist in the database.
     */
    const STATE_NEW = 'STATE_NEW';

    /**
     * Entity exists in the database, but data mapper only
     * has its id.
     */
    const STATE_KNOWN = 'STATE_KNOWN';

    /**
     * Entity exists in the database and is fully loaded in the
     * unit of work.
     */
    const STATE_MANAGED = 'STATE_MANAGED';

    /**
     * Observers of the observable pattern.
     *
     * @var array
     */
    private $observers = [];

    /**
     * @var DataMapper
     */
    private $dm;

    /**
     * Links every entity's hash with its object.
     *
     * @var array
     */
    private $entities = [];

    /**
     * Represents every known id associated with its object hash, sorted by class.
     * If the entity state is known, the id will be associated to null.
     *
     * @var array
     */
    private $idMap = [];

    /**
     * Links every entity's hash to its id.
     *
     * @var array
     */
    private $ids = [];

    /**
     * Links every entity's hash to its original data.
     *
     * @var array
     */
    private $originalData = [];

    /**
     * Links every entity's hash with its current state.
     *
     * @var array
     */
    private $states = [];

    /**
     * @var array
     */
    private $visitedEntities = [];

    /**
     * Sorted by class, then by hash.
     *
     * @var array
     */
    private $scheduledInsertions = [];

    /**
     * Sorted by class.
     *
     * @var array
     */
    private $scheduledUpdates = [];

    /**
     * Sorted by class, then by hash.
     *
     * @var array
     */
    private $scheduledRemovals = [];

    /**
     * Sorted by class.
     *
     * @var array
     */
    private $changeSets = [];

    /**
     * Entity persisters with classes as keys.
     *
     * @var array
     */
    private $entityPersisters = [];

    /**
     * @param DataMapper $dm
     */
    public function __construct(DataMapper $dm)
    {
        $this->dm = $dm;
    }

    /**
     * @param $oid
     * @return array
     */
    public function findId($oid)
    {
        return isset($this->ids[$oid]) ? $this->ids[$oid] : null;
    }

    /**
     * Adds the fully loaded entity to the unit of work.
     *
     * @param $entity
     * @param array $data
     */
    public function addManaged($entity, array $data)
    {
        $oid = spl_object_hash($entity);

        if (isset($this->states[$oid]))
        {
            switch ($this->states[$oid])
            {
                case self::STATE_MANAGED:
                    return;
                case self::STATE_KNOWN:
                    // ...
                    break;
            }
        }

        $class = get_class($entity);
        $metadata = $this->dm->getMetadata($class);
        $id = $data[$metadata->primaryKey()->name()];

        $this->entities[$oid] = $entity;

        $this->idMap[$class][$id] = $oid;

        $this->ids[$oid] = $id;

        $this->originalData[$oid] = $data;

        $this->states[$oid] = self::STATE_MANAGED;
    }

    /**
     * Adds a new entity to the unit of work
     * which does not yet exist in the database.
     *
     * @param $entity
     * @param null $oid
     */
    public function addNew($entity, $oid = null)
    {
        if (is_null($oid))
        {
            $oid = spl_object_hash($entity);
        }

        if (isset($this->states[$oid]))
        {
            return;
        }

        $this->entities[$oid] = $entity;

        $this->states[$oid] = self::STATE_NEW;

        $this->scheduleInsertion(get_class($entity), $oid);
    }

    /**
     * Marks the entity for deletion.
     * The entity must be managed by the unit of work.
     *
     * @param $entity
     * @throws DataMapperException
     */
    public function addToRemovals($entity)
    {
        $oid = spl_object_hash($entity);

        if (!isset($this->states[$oid]))
        {
            throw new DataMapperException('UnitOfWork.addToRemovals : Entity of class '.get_class($entity).
                ' cannot be removed as it is not known by the unit of work.');
        }

        $this->scheduleRemoval(get_class($entity), $oid);
    }

    /**
     * Finds the entity and loads it if necessary.
     *
     * @param $class
     * @param $id
     * @return null
     */
    public function find($class, $id)
    {
        if ($this->isLoaded($class, $id))
        {
            return $this->entities[$this->idMap[$class][$id]];
        }

        return $this->loadSingle($class, $id);
    }

    /**
     * Checks if the entity is fully loaded in the unit of work.
     *
     * @param $class
     * @param $id
     * @return bool
     */
    private function isLoaded($class, $id)
    {
        return isset($this->idMap[$class][$id]) &&
        $this->states[$this->idMap[$class][$id]] == self::STATE_MANAGED;
    }

    /**
     * Loads a single entity from the database.
     *
     * @param $class
     * @param $id
     * @return mixed|null
     */
    public function loadSingle($class, $id)
    {
        $metadata = $this->dm->getMetadata($class);

        $data = $this->dm->queryBuilder()->table($metadata->table())
            ->where($metadata->primaryKey()->propName(), '=', $id)
            ->select();

        $result = $this->processFoundData($class, $data);

        return sizeof($result) == 0 ? null : $result[0];
    }

    /**
     * @param $class
     * @param array $ids
     * @return array
     */
    public function loadMany($class, array $ids)
    {
        if (sizeof($ids) == 0)
        {
            return [];
        }

        $metadata = $this->dm->getMetadata($class);

        $allData = $this->dm->queryBuilder()->table($metadata->table())
            ->where($metadata->primaryKey()->propName(), 'in', '('.implode(',', $ids).')')
            ->select();

        return $this->processFoundData($class, $allData);
    }

    /**
     * @param $class
     * @param array $allData
     * @return array
     */
    public function processFoundData($class, array $allData)
    {
        if (sizeof($allData) == 0)
        {
            return [];
        }

        $result = [];
        foreach ($allData as $data)
        {
            $entity = $this->buildEntity($class, $data);

            $result[] = $entity;

            $this->addManaged($entity, $data);
        }

        return $result;
    }

    /**
     * Builds an entity from raw data from the database.
     *
     * @param $class
     * @param array $data
     * @return mixed
     */
    private function buildEntity($class, array $data)
    {
        $metadata = $this->dm->getMetadata($class);
        $r = $metadata->getReflectionClass();
        $entity = $r->newInstanceWithoutConstructor();

        foreach ($metadata->columns() as $column)
        {
            if ($column->isForeignKey())
            {
                continue;
            }

            $value = $data[$column->name()];

            if ($column->isBoolean())
            {
                $value = $value == 1 ? true : false;
            }

            if ($column->isDateTime())
            {
                $value = Carbon::parse($value);
            }

            if ($column->isJson())
            {
                $value = json_decode($value, true);
            }

            $metadata->reflProp($column->propName())->setValue($entity, $value);
        }

        $this->buildAssociations($entity, $metadata, $data);

        return $entity;
    }

    private function buildAssociations($entity, Metadata $metadata, array $data)
    {
        foreach ($metadata->associations() as $association)
        {
            switch ($association->type())
            {
                case Metadata::ASSOC_HAS_ONE:
                    $this->buildHasOne($entity, $metadata, $association, $data);
                    break;
                case Metadata::ASSOC_BELONGS_TO:
                    $this->buildBelongsTo($entity, $metadata, $association, $data);
                    break;
                case Metadata::ASSOC_HAS_MANY:
                    $this->buildHasMany($entity, $metadata, $association, $data);
                    break;
            }
        }
    }

    private function buildHasOne($entity, Metadata $metadata, Association $association, array $data)
    {
        $value = null;

        if ($association->isLazy())
        {
            $value = new ProxyEntity($this, $association, 0,
                $metadata->className(), $data[$metadata->primaryKey()->name()]);
        }
        else
        {
            $value = $this->resolveHasOneEntity($metadata->className(), $data[$metadata->primaryKey()->name()],
                $association->target());
        }

        if (!is_null($value))
        {
            $metadata->reflProp($association->propName())->setValue($entity, $value);
        }
    }

    private function buildBelongsTo($entity, Metadata $metadata, Association $association, array $data)
    {
        if (is_null($data[$association->column()->name()]))
        {
            return;
        }

        $value = null;

        if ($this->isLoaded($association->target(), $data[$association->column()->name()]))
        {
            $value = $this->entities[$this->idMap[$association->target()][$data[$association->column()->name()]]];
        }
        else if ($association->isLazy())
        {
            $value = new ProxyEntity($this, $association, $data[$association->column()->name()],
                $metadata->className(), $data[$metadata->primaryKey()->name()]);
        }
        else
        {
            $value = $this->loadSingle($association->target(), $data[$association->column()->name()]);
        }

        if (!is_null($value))
        {
            $metadata->reflProp($association->propName())->setValue($entity, $value);
        }
    }

    private function buildHasMany($entity, Metadata $metadata, Association $association, array $data)
    {
        $assocMetadata = $this->dm->getMetadata($association->target());

        $ids = $this->dm->queryBuilder()->table($assocMetadata->table())
            ->where($metadata->generateForeignKeyName(), '=', $data[$metadata->primaryKey()->name()])
            ->select([$assocMetadata->primaryKey()->name()]);

        if (sizeof($ids) == 0)
        {
            $metadata->reflProp($association->propName())->setValue(
                $entity, new PersistentCollection($this->dm, $association->target()));

            return;
        }

        $items = [];
        foreach ($ids as $id)
        {
            if ($this->isLoaded($association->target(), $id))
            {
                $items[$id] = $this->entities[$this->idMap[$association->target()][$id]];

                continue;
            }

            $items[$id] = null;
        }

        $metadata->reflProp($association->propName())->setValue(
            $entity, new PersistentCollection($this->dm, $association->target(), $items));
    }

    public function resolveHasOneEntity($parentClass, $parentId, $assocClass)
    {
        $metadata = $this->dm->getMetadata($parentClass);
        $assocMetadata = $this->dm->getMetadata($assocClass);

        $results = $this->dm->queryBuilder()->table($assocMetadata->table())
            ->where($metadata->generateForeignKeyName(), '=', $parentId)
            ->select();

        return sizeof($results) > 0 ? $this->buildEntity($assocClass, $results[0]) : null;
    }

    /**
     * Detach an entity.
     *
     * @param $entity
     */
    public function detach($entity)
    {
        $oid = spl_object_hash($entity);

        if (!isset($this->states[$oid]))
        {
            return;
        }

        switch ($this->states[$oid])
        {
            case self::STATE_NEW:
                $this->detachNew($oid);
                break;
            case self::STATE_KNOWN:
                $this->detachKnown($oid);
                break;
            case self::STATE_MANAGED:
                $this->detachManaged($oid);
                break;
        }
    }

    /**
     * Detaches a managed entity.
     *
     * @param $oid
     */
    private function detachManaged($oid)
    {
        $entity = $this->entities[$oid];
        $class = get_class($entity);
        $metadata = $this->dm->getMetadata($class);
        $id = $metadata->reflProp($metadata->primaryKey()->name())->getValue($entity);

        unset($this->entities[$oid]);
        unset($this->ids[$oid]);
        unset($this->originalData[$oid]);
        unset($this->idMap[$class][$id]);
        unset($this->states[$oid]);
    }

    /**
     * Detaches a known entity.
     *
     * @param $oid
     */
    private function detachKnown($oid)
    {
        unset($this->ids[$oid]);
        unset($this->states[$oid]);
    }

    /**
     * Detaches a new entity.
     *
     * @param $oid
     */
    private function detachNew($oid)
    {
        unset($this->entities[$oid]);
        unset($this->states[$oid]);
    }

    /**
     * Detaches all entities from the unit of work.
     */
    public function detachAll()
    {
        $this->entities = [];
        $this->idMap = [];
        $this->ids = [];
        $this->originalData = [];
        $this->states = [];
        $this->scheduledInsertions = [];
        $this->scheduledUpdates = [];
        $this->scheduledRemovals = [];
    }

    /**
     * Schedules the entity for insertion.
     *
     * @param $class
     * @param $oid
     */
    public function scheduleInsertion($class, $oid)
    {
        $this->scheduledInsertions[$class][] = $oid;
    }

    /**
     * Schedules the entity for update.
     *
     * @param $class
     * @param $oid
     */
    public function scheduleUpdate($class, $oid)
    {
        $this->scheduledUpdates[$class][] = $oid;
    }

    /**
     * Schedules the entity for deletion.
     *
     * @param $class
     * @param $oid
     */
    public function scheduleRemoval($class, $oid)
    {
        $this->scheduledRemovals[$class][] = $oid;
    }

    /**
     * Adds a change set for updates.
     *
     * @param $oid
     * @param $changeSet
     */
    private function addChangeSet($oid, $changeSet)
    {
        $this->changeSets[$oid] = $changeSet;
    }

    /**
     * Gets the class entity persister.
     *
     * @param $class
     * @return EntityPersister
     */
    private function getEntityPersister($class)
    {
        if (!isset($this->entityPersisters[$class]))
        {
            $this->entityPersisters[$class] = new EntityPersister($this->dm, $this, $class);
        }

        return $this->entityPersisters[$class];
    }

    /**
     * Figures out the dependecies for all classes and
     * builds an order in which they must be commited.
     *
     * @param array $classes
     * @param bool $reverse
     * @return array
     */
    private function getCommitOrder(array $classes, $reverse = false)
    {
        $classes = array_flip($classes);

        foreach ($classes as $class => $number)
        {
            foreach ($this->dm->getMetadata($class)->associations() as $association)
            {
                $assocClass = $association->target();
                if (!isset($classes[$assocClass]))
                {
                    continue;
                }

                switch ($association->type())
                {
                    case Metadata::ASSOC_HAS_ONE:
                        continue 2;
                    case Metadata::ASSOC_BELONGS_TO:
                        if ($classes[$assocClass] < $number)
                        {
                            continue 2;
                        }
                        break;
                    case Metadata::ASSOC_HAS_MANY:
                        if ($classes[$assocClass] > $number)
                        {
                            continue 2;
                        }
                        break;
                }

                $classes[$class] = $classes[$assocClass];
                $classes[$assocClass] = $number;
            }
        }

        $classes = array_flip($classes);

        $reverse ? krsort($classes) : ksort($classes);

        return $classes;
    }

    /**
     * Executes all scheduled work in the unit of work.
     */
    public function commit()
    {
        $this->dm->queryBuilder()->beginTransaction();

        try
        {
            foreach ($this->getCommitOrder(array_keys($this->scheduledInsertions)) as $class)
            {
                $this->executeInsertions($class);
            }

            $this->detectEntityChanges();

            foreach ($this->getCommitOrder(array_keys($this->scheduledUpdates)) as $class)
            {
                $this->executeUpdates($class);
            }

            $this->processCascadeRemovals();

            foreach ($this->getCommitOrder(array_keys($this->scheduledRemovals), true) as $class)
            {
                $this->executeRemovals($class);
            }

            $this->dm->queryBuilder()->commit();

            $this->notify(self::EVENT_COMMITED);
        }
        catch (Exception $e)
        {
            $this->dm->queryBuilder()->rollBack();

            throw new DataMapperException('UnitOfWork.commit : '.$e->getMessage());
        }
    }

    /**
     * Executes all insertions
     *
     * @param $class
     */
    private function executeInsertions($class)
    {
        $persister = $this->getEntityPersister($class);
        $metadata = $this->dm->getMetadata($class);
        $oids = $this->scheduledInsertions[$class];

        foreach ($oids as $oid)
        {
            $this->visitedEntities[$oid] = null;

            $data = $this->prepareInsertionData($metadata, $oid);

            $this->originalData[$oid] = $data;

            $persister->addInsert($oid, $data);
        }

        $ids = $persister->executeInserts();

        foreach ($oids as $oid)
        {
            $metadata->reflProp($metadata->primaryKey()->name())->setValue($this->entities[$oid], $ids[$oid]);
            if ($metadata->hasCreatedAt())
            {
                $metadata->reflProp($metadata->createdAt()->propName())
                    ->setValue($this->entities[$oid], $this->originalData[$oid][$metadata->createdAt()->name()]);
            }
            if ($metadata->hasUpdatedAt())
            {
                $metadata->reflProp($metadata->updatedAt()->propName())
                    ->setValue($this->entities[$oid], $this->originalData[$oid][$metadata->updatedAt()->name()]);
            }

            $this->originalData[$oid][$metadata->primaryKey()->name()] = $ids[$oid];
            $this->addManaged($this->entities[$oid], $this->originalData[$oid]);
        }

        unset($this->scheduledInsertions[$class]);
    }

    private function prepareInsertionData(Metadata $metadata, $oid)
    {
        $data = [];

        foreach ($metadata->columns() as $column)
        {
            if ($column->isPrimaryKey() || $column->isForeignKey())
            {
                continue;
            }

            $value = $metadata->reflProp($column->propName())->getValue($this->entities[$oid]);

            if ($column->isTimestamp() && is_null($value))
            {
                $value = Carbon::now();
            }

            if ($column->isJson())
            {
                $data[$column->name()] = is_null($value) ? '[]' : json_encode($value);
                continue;
            }

            if (is_null($value) && $column->isDefault())
            {
                $value = $column->defaultValue();
                $metadata->reflProp($column->propName())->setValue($this->entities[$oid], $value);
            }

            $data[$column->name()] = $value;
        }

        foreach ($this->processAssociations($oid, $metadata) as $columnName => $value)
        {
            $data[$columnName] = $value;
        }

        return $data;
    }

    private function processAssociations($oid, Metadata $metadata)
    {
        $assocData = [];

        foreach ($metadata->associations() as $association)
        {
            switch ($association->type())
            {
                case Metadata::ASSOC_BELONGS_TO:
                    $assocData[$association->column()->name()] = $this->processBelongsTo($oid, $metadata, $association);
                    break;
                case Metadata::ASSOC_HAS_MANY:
                    $this->processHasMany($oid, $metadata, $association);
                    break;
            }
        }

        return $assocData;
    }

    /**
     * @param $oid
     * @param Metadata $metadata
     * @param Association $association
     * @return null|integer
     * @throws DataMapperException
     */
    private function processBelongsTo($oid, Metadata $metadata, Association $association)
    {
        $column = $association->column();
        $assocValue = $metadata->reflProp($column->propName())->getValue($this->entities[$oid]);

        if ($assocValue instanceof ProxyEntity)
        {
            return $assocValue->getId();
        }

        if (is_null($assocValue))
        {
            if ($association->isNullable())
            {
                return null;
            }

            throw new DataMapperException('UnitOfWork.processBelongsToForInsert : Association on '
                .$column->propName().' cannot be null.');
        }

        $assocOid = spl_object_hash($assocValue);

        if (!isset($this->states[$assocOid]))
        {
            throw new DataMapperException('UnitOfWork.processBelongsToForInsert : Associated entity for '
                .$column->propName().' is not known to datamapper.');
        }

        if ($this->states[$assocOid] == self::STATE_NEW)
        {
            throw new DataMapperException('UnitOfWork.processBelongsToForInsert : Commit order error. '
                .'Associated entity '.$column->propName().' is not yet persisted.');
        }

        return $this->ids[$assocOid];
    }

    /**
     * @param $oid
     * @param Metadata $metadata
     * @param Association $association
     * @return null|integer
     */
    private function processHasMany($oid, Metadata $metadata, Association $association)
    {
        $assocValue = $metadata->reflProp($association->propName())->getValue($this->entities[$oid]);

        if (is_null($assocValue) || !($assocValue instanceof EntityCollection))
        {
            return;
        }

        $this->processEntityCollection($oid, $assocValue, $metadata, $association);
    }

    /**
     * @param $oid
     * @param EntityCollection $assocValue
     * @param Metadata $metadata
     * @param Association $association
     */
    private function processEntityCollection($oid, $assocValue, Metadata $metadata, Association $association)
    {
        $items = [];
        foreach ($assocValue->toArray() as $item)
        {
            if (is_null($item))
            {
                continue;
            }

            $itemOid = spl_object_hash($item);

            if (!isset($this->states[$itemOid]))
            {
                continue;
            }

            $items[$itemOid] = $item;
        }

        $collection = new PersistentCollection($this->dm, $association->target(), $items);
        $metadata->reflProp($association->propName())->setValue($this->entities[$oid], $collection);
    }

    /**
     * Executes all removals.
     *
     * @param $class
     */
    private function executeRemovals($class)
    {
        $persister = $this->getEntityPersister($class);

        foreach ($this->scheduledRemovals[$class] as $oid)
        {
            $persister->addRemoval($this->ids[$oid]);
            $this->detachManaged($oid);
        }

        $persister->executeRemovals();

        unset($this->scheduledRemovals[$class]);
    }

    /**
     * Checks for any assocations marked for cascade removal.
     */
    private function processCascadeRemovals()
    {
        $currentRemovals = $this->scheduledRemovals;
        foreach ($currentRemovals as $class => $oids)
        {
            $metadata = $this->dm->getMetadata($class);

            foreach ($oids as $oid)
            {
                $this->processSingleEntityCascadeRemovals($metadata, $oid);
            }
        }
    }

    /**
     * Processes cascade removals recursively starting from a single entity.
     *
     * @param Metadata $metadata
     * @param $oid
     */
    private function processSingleEntityCascadeRemovals(Metadata $metadata, $oid)
    {
        foreach ($metadata->associations() as $association)
        {
            if (!$association->hasDeleteCascade())
            {
                continue;
            }

            $assocValue = $metadata->reflProp($association->propName())->getValue($this->entities[$oid]);

            if ($assocValue instanceof PersistentCollection)
            {
                foreach ($assocValue as $entity)
                {
                    $entityOid = spl_object_hash($entity);

                    if (isset($this->scheduledRemovals[$association->target()]) &&
                        isset($this->scheduledRemovals[$association->target()][$entityOid]))
                    {
                        continue;
                    }

                    $entityMetadata = $this->dm->getMetadata($association->target());

                    $this->scheduleRemoval($entityMetadata->className(), $entityOid);

                    $this->processSingleEntityCascadeRemovals($entityMetadata, $entityOid);
                }

                continue;
            }

            if (is_null($assocValue))
            {
                continue;
            }

            if ($assocValue instanceof ProxyEntity)
            {
                $assocValue = $assocValue->resolve();
            }

            $assocOid = spl_object_hash($assocValue);

            if (isset($this->scheduledRemovals[$metadata->className()]) &&
                isset($this->scheduledRemovals[$metadata->className()][$assocOid]))
            {
                continue;
            }

            $assocMetadata = $this->dm->getMetadata(get_class($assocValue));

            $this->scheduleRemoval($assocMetadata->className(), $assocOid);

            $this->processSingleEntityCascadeRemovals($assocMetadata, $assocOid);
        }
    }

    /**
     * Executes all scheduled updates
     *
     * @param $class
     */
    private function executeUpdates($class)
    {
        $persister = $this->getEntityPersister($class);

        foreach ($this->scheduledUpdates[$class] as $oid)
        {
            $persister->addUpdate($this->ids[$oid], $this->changeSets[$oid]);
        }

        $persister->executeUpdates();

        unset($this->scheduledUpdates[$class]);
    }

    /**
     * Loops through all managed entities or a single one to detect changes
     * from the original values and schedules them from update.
     */
    private function detectEntityChanges()
    {
        foreach ($this->states as $oid => $state)
        {
            if ($state != self::STATE_MANAGED || array_key_exists($oid, $this->visitedEntities))
            {
                continue;
            }

            $this->detectSingleEntityChanges($oid);
        }

        $this->visitedEntities = [];
    }

    /**
     * @param $oid
     * @throws DataMapperException
     */
    private function detectSingleEntityChanges($oid)
    {
        $entity = $this->entities[$oid];
        $class = get_class($entity);

        $changeSet = $this->buildChangeSet($entity, $oid);

        if (sizeof($changeSet) == 0)
        {
            return;
        }

        $this->scheduleUpdate($class, $oid);
        $this->addChangeSet($oid, $changeSet);
    }

    /**
     * Compares the entity with its original data for changes.
     *
     * @param $entity
     * @param $oid
     * @return array $changeSet
     * @throws DataMapperException
     */
    private function buildChangeSet($entity, $oid)
    {
        $metadata = $this->dm->getMetadata(get_class($entity));
        $changeSet = [];
        $originalData = $this->originalData[$oid];

        foreach ($metadata->columns() as $column)
        {
            if ($column->isPrimaryKey() || $column->isForeignKey())
            {
                continue;
            }

            $actualValue = $metadata->reflProp($column->propName())->getValue($entity);

            if ($column->isJson())
            {
                $actualValue = json_encode($actualValue);
            }

            if ($originalData[$column->name()] == $actualValue)
            {
                continue;
            }

            $changeSet[$column->name()] = $actualValue;
        }

        foreach ($this->processAssociations($oid, $metadata) as $columnName => $actualValue)
        {
            if ($actualValue != $originalData[$columnName])
            {
                $changeSet[$columnName] = $actualValue;
            }
        }

        if (sizeof($changeSet) > 0 && $metadata->hasUpdatedAt())
        {
            $now = Carbon::now();
            $changeSet[$metadata->updatedAt()->name()] = $now;
            $metadata->reflProp($metadata->updatedAt()->propName())->setValue($entity, $now);
        }

        return $changeSet;
    }

    public function replaceProxy(string $parentClass, string $parentId, string $propName, $replacement)
    {
        if (!isset($this->idMap[$parentClass][$parentId]))
        {
            return;
        }

        $metadata = $this->dm->getMetadata($parentClass);
        $metadata->reflProp($propName)->setValue($this->entities[$this->idMap[$parentClass][$parentId]], $replacement);
    }

    public function subscribe(Observer $observer)
    {
        $this->observers[] = $observer;
    }

    private function notify($event)
    {
        foreach ($this->observers as $observer)
        {
            $observer->update($event);
        }
    }
}