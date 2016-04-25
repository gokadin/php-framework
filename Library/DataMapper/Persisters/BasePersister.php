<?php

namespace Library\DataMapper\Persisters;

use Library\DataMapper\DataMapper;
use Library\DataMapper\UnitOfWork\UnitOfWork;

/**
 * Responsible for the interactions between entities
 * of one class and the database.
 */
abstract class BasePersister
{
    /**
     * The data mapper instance.
     *
     * @var DataMapper
     */
    protected $dm;

    /**
     * The unit of work.
     *
     * @var UnitOfWork
     */
    protected $unitOfWork;

    /**
     * The class the persister is responsible for.
     *
     * @var string
     */
    protected $class;

    /**
     * The class metadata.
     *
     * @var Metadata
     */
    protected $metadata;

    /**
     * The data mapper instance of query builder.
     *
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @param DataMapper $dm
     * @param UnitOfWork $unitOfWork
     * @param $class
     */
    public function __construct(DataMapper $dm, UnitOfWork $unitOfWork, $class)
    {
        $this->dm = $dm;
        $this->unitOfWork = $unitOfWork;
        $this->class = $class;
        $this->queryBuilder = $dm->queryBuilder();
        $this->metadata = $dm->getMetadata($class);
    }
}