<?php

namespace Tests\TestData\DataMapper;

/**
 * @Entity
 */
class Address
{
    /** @Id */
    protected $id;

    /** @Column(type="string") */
    protected $street;

    /** @BelongsTo(target="Tests\TestData\DataMapper\Teacher", nullable="true") */
    private $teacher;

    public function __construct($street)
    {
        $this->street = $street;
    }

    public function getId()
    {
        return $this->id;
    }

    public function street()
    {
        return $this->street;
    }

    public function setStreet($street)
    {
        $this->street = $street;
    }

    public function teacher()
    {
        return $this->teacher;
    }

    public function setTeacher(Teacher $teacher)
    {
        $this->teacher = $teacher;
    }
}