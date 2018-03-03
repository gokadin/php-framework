<?php

namespace Tests\Library\Engine\Schema;

use Library\Engine\Schema\SchemaSynchronizer;
use Tests\BaseTest;

class SchemaSynchronizerTest extends BaseTest
{
    private const USER_MODEL_CLASS = 'Tests\\App\\SchemaTestModels\\User';
    private const USER_CONTROLLER_CLASS = 'Tests\\App\\Http\\SchemaTestControllers\\UserController';
    private const POST_MODEL_CLASS = 'Tests\\App\\SchemaTestModels\\Post';
    private const ADDRESS_MODEL_CLASS = 'Tests\\App\\SchemaTestModels\\Address';

    /**
     * @var string
     */
    private $datamapperConfigFile;

    /**
     * @var SchemaSynchronizer
     */
    private $synchronizer;

    public function setUp()
    {
        parent::setUp();

        $this->datamapperConfigFile = $this->basePath().'/Config/FeaturesConfig/schemaTestDatamapper.php';

        $this->synchronizer = new SchemaSynchronizer($this->basePath().'/..', [
            'modelsPath' => 'Tests/App/SchemaTestModels',
            'controllersPath' => 'Tests/App/Http/SchemaTestControllers'
        ], $this->datamapperConfigFile);
    }

    /**
     * CREATE
     */

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

        unlink($this->datamapperConfigFile);
        $template = file_get_contents($this->basePath().'/Config/FeaturesConfig/schemaTestDatamapperTemplate.php');
        file_put_contents($this->datamapperConfigFile, $template);
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

    public function test_synchronize_classIsAddedToDataMapperConfig()
    {
        // Act
        $this->synchronizer->synchronize([
            'user' => [
                'name' => ['type' => 'string']
            ]
        ], []);

        // Assert
        $config = require $this->datamapperConfigFile;
        $classes = $config['classes'];
        $this->assertEquals(1, sizeof($classes));
        $this->assertEquals(self::USER_MODEL_CLASS, $classes[0]);
    }

    /**
     * UPDATE
     */

    public function test_synchronize_additionalClassIsAddedToDataMapperConfig()
    {
        // Arrange
        $this->synchronizer->synchronize([
            'user' => [
                'name' => ['type' => 'string']
            ]
        ], []);

        // Act
        $this->synchronizer->synchronize([
            'post' => [
                'title' => ['type' => 'string']
            ]
        ], []);

        // Assert
        $config = require $this->datamapperConfigFile;
        $classes = $config['classes'];
        $this->assertEquals(2, sizeof($classes));
        $this->assertEquals(self::USER_MODEL_CLASS, $classes[0]);
        $this->assertEquals(self::POST_MODEL_CLASS, $classes[1]);
    }

    public function test_synchronize_classIsRemovedFromDataMapperConfig()
    {
        // Arrange
        $previousSchema = [
            'user' => [
                'name' => ['type' => 'string']
            ],
            'post' => [
                'title' => ['type' => 'string']
            ]
        ];
        $this->synchronizer->synchronize($previousSchema, []);

        // Assert
        $config = require $this->datamapperConfigFile;
        $classes = $config['classes'];
        $this->assertEquals(2, sizeof($classes));
        $this->assertEquals(self::USER_MODEL_CLASS, $classes[0]);
        $this->assertEquals(self::POST_MODEL_CLASS, $classes[1]);

        // Act
        $this->synchronizer->synchronize([
            'post' => [
                'title' => ['type' => 'string']
            ]
        ], $previousSchema);

        // Assert
        $config = require $this->datamapperConfigFile;
        $classes = $config['classes'];
        $this->assertEquals(1, sizeof($classes));
        $this->assertEquals(self::POST_MODEL_CLASS, $classes[0]);
    }

    /**
     * RELATIONSHIPS
     */

    public function test_synchronize_modelHasHasOneProperty()
    {
        // Act
        $this->synchronizer->synchronize([
            'user' => [
                'address' => ['hasOne' => 'address']
            ]
        ], []);

        // Assert
        $r = new \ReflectionClass(self::USER_MODEL_CLASS);
        $this->assertTrue($r->hasProperty('address'));
    }

    public function test_synchronize_modelHasHasOnePropertyAnnotations()
    {
        // Act
        $this->synchronizer->synchronize([
            'user' => [
                'address' => ['hasOne' => 'address']
            ]
        ], []);

        // Assert
        $r = new \ReflectionClass(self::USER_MODEL_CLASS);
        $this->assertTrue($r->hasProperty('address'));
        $doc = $r->getProperty('address')->getDocComment();
        $this->assertNotFalse(strpos($doc, '@HasOne(target="'.self::ADDRESS_MODEL_CLASS.'")'));
    }

    public function test_synchronize_modelHasHasManyProperty()
    {
        // Act
        $this->synchronizer->synchronize([
            'user' => [
                'posts' => ['hasMany' => 'post']
            ]
        ], []);

        // Assert
        $r = new \ReflectionClass(self::USER_MODEL_CLASS);
        $this->assertTrue($r->hasProperty('posts'));
    }

    public function test_synchronize_modelHasHasManyPropertyAnnotations()
    {
        // Act
        $this->synchronizer->synchronize([
            'user' => [
                'posts' => ['hasMany' => 'post']
            ]
        ], []);

        // Assert
        $r = new \ReflectionClass(self::USER_MODEL_CLASS);
        $this->assertTrue($r->hasProperty('posts'));
        $doc = $r->getProperty('posts')->getDocComment();
        $this->assertNotFalse(strpos($doc, '@HasMany(target="'.self::POST_MODEL_CLASS.'")'));
    }
}