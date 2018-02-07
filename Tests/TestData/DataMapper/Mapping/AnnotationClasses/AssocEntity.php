<?php

namespace Tests\TestData\DataMapper\Mapping\AnnotationClasses;

/** @Entity */
class AssocEntity
{
    /** @HasOne(target="test") */
    private $assocHasOne;
}