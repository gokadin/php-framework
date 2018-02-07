<?php

namespace Tests\TestData\DataMapper;

use Library\DataMapper\Collection\EntityCollection;
use Library\DataMapper\DataMapperTimestamps;

/** @Entity */
class Student
{
    use DataMapperTimestamps;

    /** @Id */
    protected $id;

    /** @Column(type="string", size="50") */
    protected $name;

    /** @BelongsTo(target="Tests\TestData\DataMapper\Teacher") */
    protected $teacher;

    /** @HasMany(target="Tests\TestData\DataMapper\Lesson", mappedBy="student", nullable) */
    protected $lessons;

    public function __construct($name, $teacher)
    {
        $this->name = $name;
        $this->teacher = $teacher;
        $this->lessons = new EntityCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function name()
    {
        return $this->name;
    }

    public function setTeacher($teacher)
    {
        $this->teacher = $teacher;
    }

    public function teacher()
    {
        return $this->teacher;
    }

    public function lessons()
    {
        return $this->lessons;
    }

    public function addLesson($lesson)
    {
        $this->lessons->add($lesson);
    }

    public function removeLesson($lesson)
    {
        $this->lessons->remove($lesson);
    }
}