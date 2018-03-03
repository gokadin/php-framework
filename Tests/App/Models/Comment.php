<?php

namespace Tests\App\Models;

use Library\DataMapper\DataMapperPrimaryKey;
use Library\DataMapper\DataMapperTimestamps;

/** @Entity */
class Comment
{
    use DataMapperPrimaryKey, DataMapperTimestamps;

    /** @Column(type="string") */
    private $text;

    /** @BelongsTo(target="Tests\App\Models\Post") */
    private $post;

    public function __construct($text, $post)
    {
        $this->text = $text;
        $this->post = $post;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function getPost()
    {
        return $this->post;
    }

    public function setPost($post)
    {
        $this->post = $post;
    }
}