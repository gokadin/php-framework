<?php

namespace Tests\App\SchemaTestModels;

use Library\DataMapper\DataMapperPrimaryKey;
use Library\DataMapper\DataMapperTimestamps;

/** @Entity */
class User
{
    use DataMapperPrimaryKey, DataMapperTimestamps;

    /** @BelongsTo(target="Tests\App\SchemaTestModels\Post") */
    private $post;

    public function __construct() {

    }

    public function getPost() {
        return $this->post;
    }

    public function setPost($value) {
        $this->post = $value;
    }
}
