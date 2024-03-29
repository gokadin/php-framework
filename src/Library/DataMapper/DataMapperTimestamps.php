<?php

namespace Library\DataMapper;

trait DataMapperTimestamps
{
    /** @Column(name="created_at", type="datetime") */
    private $createdAt;

    /** @Column(name="updated_at", type="datetime") */
    private $updatedAt;

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }
}