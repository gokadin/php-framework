<?php

namespace Tests\Library\Engine\Schema;

use Library\Engine\Schema\SchemaSynchronizer;
use ReflectionException;
use Tests\BaseTest;

class SchemaSynchronizerTest extends BaseTest
{
    private const USER_MODEL_CLASS = '\\Tests\\App\\SchemaTestModels\\User';
    private const USER_CONTROLLER_CLASS = '\\Tests\\App\\Http\\SchemaTestControllers\\UserController';

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
        $this->assertTrue(class_exists(self::USER_MODEL_CLASS));
    }

    public function test_synchronize_modelHasEntityAnnotation()
    {
        // Act
        $this->synchronizer->synchronize([
            'user' => [
                'name' => ['type' => 'string']
            ]
        ], []);

        // Assert
        $r = new \ReflectionClass(self::USER_MODEL_CLASS);
        $this->assertNotFalse(strpos($r->getDocComment(), '@Entity'));
    }

    public function test_synchronize_modelHasIdProperty()
    {
        // Act
        $this->synchronizer->synchronize([
            'user' => [
                'name' => ['type' => 'string']
            ]
        ], []);

        // Assert
        $r = new \ReflectionClass(self::USER_MODEL_CLASS);
        $this->assertTrue($r->hasProperty('id'));
    }

    public function test_synchronize_modelHasIdGetter()
    {
        // Act
        $this->synchronizer->synchronize([
            'user' => [
                'name' => ['type' => 'string']
            ]
        ], []);

        // Assert
        $r = new \ReflectionClass(self::USER_MODEL_CLASS);
        $this->assertTrue($r->hasMethod('getId'));
    }

    /**
     * @
     */
    public function test_synchronize_modelHasNoIdSetter()
    {
        // Act
        $this->synchronizer->synchronize([
            'user' => [
                'name' => ['type' => 'string']
            ]
        ], []);

        // Assert
        $r = new \ReflectionClass(self::USER_MODEL_CLASS);
        $this->assertFalse($r->hasMethod('setId'));
    }

    public function test_synchronize_modelHasCreatedAtProperty()
    {
        // Act
        $this->synchronizer->synchronize([
            'user' => [
                'name' => ['type' => 'string']
            ]
        ], []);

        // Assert
        $r = new \ReflectionClass(self::USER_MODEL_CLASS);
        $this->assertTrue($r->hasProperty('createdAt'));
    }

    public function test_synchronize_modelHasCreatedAtGetter()
    {
        // Act
        $this->synchronizer->synchronize([
            'user' => [
                'name' => ['type' => 'string']
            ]
        ], []);

        // Assert
        $r = new \ReflectionClass(self::USER_MODEL_CLASS);
        $this->assertTrue($r->hasMethod('getCreatedAt'));
    }

    public function test_synchronize_modelHasCreatedAtSetter()
    {
        // Act
        $this->synchronizer->synchronize([
            'user' => [
                'name' => ['type' => 'string']
            ]
        ], []);

        // Assert
        $r = new \ReflectionClass(self::USER_MODEL_CLASS);
        $this->assertTrue($r->hasMethod('setCreatedAt'));
    }

    public function test_synchronize_modelHasPrivateUpdatedAtProperty()
    {
        // Act
        $this->synchronizer->synchronize([
            'user' => [
                'name' => ['type' => 'string']
            ]
        ], []);

        // Assert
        $r = new \ReflectionClass(self::USER_MODEL_CLASS);
        $this->assertTrue($r->hasProperty('updatedAt'));
    }

    public function test_synchronize_modelHasUpdatedAtGetter()
    {
        // Act
        $this->synchronizer->synchronize([
            'user' => [
                'name' => ['type' => 'string']
            ]
        ], []);

        // Assert
        $r = new \ReflectionClass(self::USER_MODEL_CLASS);
        $this->assertTrue($r->hasMethod('getUpdatedAt'));
    }

    public function test_synchronize_modelHasUpdatedAtSetter()
    {
        // Act
        $this->synchronizer->synchronize([
            'user' => [
                'name' => ['type' => 'string']
            ]
        ], []);

        // Assert
        $r = new \ReflectionClass(self::USER_MODEL_CLASS);
        $this->assertTrue($r->hasMethod('setUpdatedAt'));
    }

    public function test_synchronize_modelHasNameProperty()
    {
        // Act
        $this->synchronizer->synchronize([
            'user' => [
                'name' => ['type' => 'string']
            ]
        ], []);

        // Assert
        $r = new \ReflectionClass(self::USER_MODEL_CLASS);
        $this->assertTrue($r->hasProperty('name'));
    }

    public function test_synchronize_modelHasNameGetter()
    {
        // Act
        $this->synchronizer->synchronize([
            'user' => [
                'name' => ['type' => 'string']
            ]
        ], []);

        // Assert
        $r = new \ReflectionClass(self::USER_MODEL_CLASS);
        $this->assertTrue($r->hasMethod('getName'));
    }

    public function test_synchronize_modelHasNameSetter()
    {
        // Act
        $this->synchronizer->synchronize([
            'user' => [
                'name' => ['type' => 'string']
            ]
        ], []);

        // Assert
        $r = new \ReflectionClass(self::USER_MODEL_CLASS);
        $this->assertTrue($r->hasMethod('setName'));
    }

    public function test_synchronize_modelPropertyNameHasCorrectAnnotation()
    {
        // Act
        $this->synchronizer->synchronize([
            'user' => [
                'name' => ['type' => 'string']
            ]
        ], []);

        // Assert
        $r = new \ReflectionClass(self::USER_MODEL_CLASS);
        $this->assertNotFalse(strpos($r->getProperty('name')->getDocComment(), '@Column(type="string")'));
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
        $this->assertTrue(class_exists(self::USER_CONTROLLER_CLASS));
    }

    public function test_synchronize_controllerExtendsFromEngineController()
    {
        // Act
        $this->synchronizer->synchronize([
            'user' => [
                'name' => ['type' => 'string']
            ]
        ], []);

        // Assert
        $r = new \ReflectionClass(self::USER_CONTROLLER_CLASS);
        $this->assertEquals('Library\\Engine\\EngineController', $r->getParentClass()->getName());
    }
}