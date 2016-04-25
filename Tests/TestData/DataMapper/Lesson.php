<?php

namespace Tests\TestData\DataMapper;
use Library\DataMapper\DataMapperPrimaryKey;

/** @Entity */
class Lesson
{
    use DataMapperPrimaryKey;

    /** @Column(type="string") */
    private $name;

    /** @BelongsTo(target="Tests\TestData\DataMapper\Student") */
    private $student;

    /** @BelongsTo(target="Tests\TestData\DataMapper\Event") */
    private $event;

    public function __construct($name, $student, $event)
    {
        $this->name = $name;
        $this->student = $student;
        $this->event = $event;
    }

    public function name()
    {
        return $this->name;
    }

    public function event()
    {
        return $this->event;
    }

    public function student()
    {
        return $this->student;
    }
}