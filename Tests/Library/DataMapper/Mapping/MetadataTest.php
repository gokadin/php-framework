<?php

namespace Tests\Library\DataMapper\Mapping;

use Library\DataMapper\Mapping\Metadata;
use Tests\BaseTest;
use ReflectionClass;
use Tests\TestData\DataMapper\Mapping\AnnotationClasses\AssocEntity;

class MetadataTest extends BaseTest
{
    /**
     * @var Metadata
     */
    private $metadata;

    public function setUpMetadata(string $class)
    {
        $this->metadata = new Metadata($class, '', new ReflectionClass($class));
    }

    public function test_addHasOneAssociation_DoesNotCreateADatabaseColumn()
    {
        // Arrange
        $this->setUpMetadata(AssocEntity::class);

        // Act
        $this->metadata->addHasOneAssociation('assocHasOne', 'assocHasOne', '', [], true, 'always');

        // Assert
        $this->assertNull($this->metadata->getColumnByPropName('assocHasOne'));
    }

    public function test_addBelongsToAssociation_CreatesADatabaseColumn()
    {
        // Arrange
        $this->setUpMetadata(AssocEntity::class);

        // Act
        $this->metadata->addBelongsToAssociation('assocBelongsTo', 'assocBelongsTo', '', [], true, 'always');

        // Assert
        $this->assertNotNull($this->metadata->getColumnByPropName('assocBelongsTo'));
    }
}