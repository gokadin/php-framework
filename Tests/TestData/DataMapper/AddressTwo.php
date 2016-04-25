<?php

namespace Tests\TestData\DataMapper;

/**
 * @Entity
 */
class AddressTwo
{
    /** @Id */
    protected $id;

    /** @Column(type="string") */
    protected $street;

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
}