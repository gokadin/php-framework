<?php

namespace Tests\Library\Engine\Schema;

use Library\Engine\Schema\SchemaSynchronizer;
use Tests\BaseTest;

class SchemaSynchronizerTest extends BaseTest
{
    /**
     * @var SchemaSynchronizer
     */
    private $synchronizer;

    public function setUp()
    {
        parent::setUp();

        $this->synchronizer = new SchemaSynchronizer($this->basePath().'/..', [
            'modelsPath' => 'Tests/App/SchemaTestModels',
            'controllersPath' => 'Tests/App/Http/SchemaTestControllers'
        ], $this->basePath().'/Config/FeaturesConfig/schemaTestDatamapper.php');
    }

    public function tearDown()
    {
        parent::tearDown();

        $files = glob($this->basePath().'/App/SchemaTestModels/*');
        $files = array_merge($files, glob($this->basePath().'/App/Http/SchemaTestControllers/*'));
        foreach($files as $file)
        {
            if(is_file($file))
            {
                unlink($file);
            }
        }
    }

    public function test_synchronize_AddsModelFile()
    {
        // Act
        $this->synchronizer->synchronize([
            'user' => [
                'name' => ['type' => 'string']
            ]
        ], []);

        // Assert
        $this->assertTrue(class_exists('\\Tests\\App\\SchemaTestModels\\User'));
    }

    public function test_synchronize_AddsControllerFile()
    {
        // Act
        $this->synchronizer->synchronize([
            'user' => [
                'name' => ['type' => 'string']
            ]
        ], []);

        // Assert
        $this->assertTrue(class_exists('\\Tests\\App\\Http\\SchemaTestControllers\\UserController'));
    }
}