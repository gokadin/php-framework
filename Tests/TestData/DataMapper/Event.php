<?php

namespace Tests\TestData\DataMapper;
use Library\DataMapper\Collection\EntityCollection;
use Library\DataMapper\DataMapperPrimaryKey;

/** @Entity */
class Event
{
    use DataMapperPrimaryKey;

    /** @Column(type="string") */
    private $name;

    /** @HasMany(target="Tests\TestData\DataMapper\Lesson", mappedBy="event") */
    private $lessons;

    public function __construct($name)
    {
        $this->name = $name;
        $this->lessons = new EntityCollection();
    }

    public function name()
    {
        return $this->name;
    }

    public function addLesson($lesson)
    {
        $this->lessons->add($lesson);
    }

    public function removeLesson($lesson)
    {
        $this->lessons->remove($lesson);
    }

    public function lessons()
    {
        return $this->lessons;
    }
}