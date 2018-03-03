<?php

namespace Tests\App\Models;

use Library\DataMapper\Collection\EntityCollection;
use Library\DataMapper\DataMapperPrimaryKey;
use Library\DataMapper\DataMapperTimestamps;

/** @Entity */
class Post
{
    use DataMapperPrimaryKey, DataMapperTimestamps;

    /** @Column(type="string") */
    private $title;

    /** @HasMany(target="Tests\App\Models\Comment", mappedBy="post") */
    private $comments;

    public function __construct(string $title)
    {
        $this->title = $title;
        $this->comments = new EntityCollection();
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function setComments($comments)
    {
        $this->comments = $comments;
    }
}