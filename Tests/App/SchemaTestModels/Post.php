<?php

namespace Tests\App\SchemaTestModels;

use Library\DataMapper\DataMapperPrimaryKey;
use Library\DataMapper\DataMapperTimestamps;
use Library\DataMapper\DataMapperPrimaryKey;
use Library\DataMapper\DataMapperTimestamps;

/** @Entity */
class Post
{
    use DataMapperPrimaryKey, DataMapperTimestamps;

    /** @Column(type="string") */
    private $name;

    /** @Column(type="string") */
    private $title;

    public function __construct() {

    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($value) {
        $this->title = $value;
    }
}
