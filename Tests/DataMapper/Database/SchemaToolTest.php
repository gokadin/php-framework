<?php

namespace FrameworkTest\DataMapper\Database;

use Library\DataMapper\Database\SchemaTool;
use Tests\BaseTest;
use Tests\TestData\DataMapper\SimpleEntity;
use Tests\TestData\DataMapper\Address;
use PDO;

class SchemaToolTest extends BaseTest
{
    protected $schemaTool;
    protected $dao;

    public function setUp()
    {
        parent::setUp();

        $config = [
            'mappingDriver' => 'annotation',

            'databaseDriver' => 'mysql',

            'mysql' => [
                'host' => env('DATABASE_HOST'),
                'database' => env('DATABASE_NAME'),
                'username' => env('DATABASE_USERNAME'),
                'password' => env('DATABASE_PASSWORD')
            ],

            'classes' => [
                SimpleEntity::class,
                Address::class
            ]
        ];

        $this->schemaTool = new SchemaTool($config);

        $this->dao = new PDO('mysql:host='.$config['mysql']['host'].';dbname='.$config['mysql']['database'],
            $config['mysql']['username'],
            $config['mysql']['password']);

        $this->dao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->schemaTool->drop();

        $this->dao = null;
    }

    protected function tableExists($table)
    {
        $query = $this->dao->query('SHOW TABLES LIKE \''.$table.'\'');
        return $query->rowCount() > 0;
    }

    public function testCreate()
    {
        // Act
        $successes = $this->schemaTool->create();

        // Assert
        $this->assertTrue($this->tableExists('simpleEntity'));
        $this->assertTrue(isset($successes['simpleEntity']));
        $this->assertTrue($successes['simpleEntity']);
    }

    public function testDrop()
    {
        // Arrange
        $this->schemaTool->create();

        // Act
        $this->schemaTool->drop();

        // Assert
        $this->assertFalse($this->tableExists('simpleEntity'));
    }

    public function testUpdateForCreatingATable()
    {
        // Arrange
        $this->schemaTool->create();
        $this->dao->exec('DROP TABLE simpleEntity');

        // Act
        $results = $this->schemaTool->update();

        // Assert
        $this->assertTrue($this->tableExists('simpleEntity'));
        $this->assertEquals('created', $results['simpleEntity']['status']);
    }

    public function testUpdateForDroppingATable()
    {
        // Arrange
        $this->schemaTool->create();
        $this->dao->exec('CREATE TABLE extra1 (col1 int(11))');

        // Act
        $results = $this->schemaTool->update(true);

        // Assert
        $this->assertFalse($this->tableExists('extra1'));
        $this->assertTrue($this->tableExists('simpleEntity'));
        $this->assertTrue($this->tableExists('Address'));
        $this->assertEquals('dropped', $results['extra1']['status']);
    }

    public function testUpdateDoesNotDropATableIfNotForced()
    {
        // Arrange
        $this->schemaTool->create();
        $this->dao->exec('CREATE TABLE extra1 (col1 int(11))');

        // Act
        $results = $this->schemaTool->update(false);

        // Assert
        $this->assertTrue($this->tableExists('extra1'));
        $this->assertTrue($this->tableExists('simpleEntity'));
        $this->assertTrue($this->tableExists('Address'));
        $this->assertEquals('unchanged', $results['extra1']['status']);
    }

    public function testUpdateForAddingAColumn()
    {
        // Arrange
        $this->schemaTool->create();
        $this->dao->exec('ALTER TABLE simpleEntity DROP one');

        // Act
        $results = $this->schemaTool->update();

        // Assert
        $this->assertTrue($this->tableExists('simpleEntity'));
        $this->assertEquals('updated', $results['simpleEntity']['status']);
        $this->assertEquals('created', $results['simpleEntity']['columns']['one']['status']);
    }

    public function testUpdateForDroppingAColumn()
    {
        // Arrange
        $this->schemaTool->create();
        $this->dao->exec('ALTER TABLE simpleEntity ADD COLUMN extra1 int(11)');

        // Act
        $results = $this->schemaTool->update(true);

        // Assert
        $this->assertTrue($this->tableExists('simpleEntity'));
        $this->assertEquals('updated', $results['simpleEntity']['status']);
        $this->assertEquals('dropped', $results['simpleEntity']['columns']['extra1']['status']);
    }

    public function testUpdateDoesNotDropAColumnIfNotForced()
    {
        // Arrange
        $this->schemaTool->create();
        $this->dao->exec('ALTER TABLE simpleEntity ADD COLUMN extra1 int(11)');

        // Act
        $results = $this->schemaTool->update(false);

        // Assert
        $this->assertTrue($this->tableExists('simpleEntity'));
        $this->assertEquals('unchanged', $results['simpleEntity']['status']);
        $this->assertFalse(isset($results['simpleEntity']['columns']));
    }

    public function testUpdateDoesNotUpdateAColumnIfForcedButNothingToUpdate()
    {
        // Arrange
        $this->schemaTool->create();

        // Act
        $results = $this->schemaTool->update(true);

        // Assert
        $this->assertTrue($this->tableExists('simpleEntity'));
        $this->assertEquals('unchanged', $results['simpleEntity']['status']);
        $this->assertFalse(isset($results['simpleEntity']['columns']));
        $this->assertEquals('unchanged', $results['Address']['status']);
        $this->assertFalse(isset($results['Address']['columns']));
    }

    public function testUpdateUpdatesAColumnIfSizeIsDifferent()
    {
        // Arrange
        $this->schemaTool->create();
        $this->dao->exec('ALTER TABLE simpleEntity DROP COLUMN one');
        $this->dao->exec('ALTER TABLE simpleEntity ADD COLUMN one INT(12)');

        // Act
        $results = $this->schemaTool->update(true);

        // Assert
        $this->assertTrue($this->tableExists('simpleEntity'));
        $this->assertEquals('updated', $results['simpleEntity']['status']);
        $this->assertEquals('updated', $results['simpleEntity']['columns']['one']['status']);
    }

    public function testUpdateDoesNotUpdateAColumnIfNotForced()
    {
        // Arrange
        $this->schemaTool->create();
        $this->dao->exec('ALTER TABLE simpleEntity DROP COLUMN one');
        $this->dao->exec('ALTER TABLE simpleEntity ADD COLUMN one INT(12)');

        // Act
        $results = $this->schemaTool->update(false);

        // Assert
        $this->assertTrue($this->tableExists('simpleEntity'));
        $this->assertEquals('unchanged', $results['simpleEntity']['status']);
        $this->assertFalse(isset($results['simpleEntity']['columns']));
    }

    public function testUpdateUpdatesAColumnIfTypeIsDifferent()
    {
        // Arrange
        $this->schemaTool->create();
        $this->dao->exec('ALTER TABLE simpleEntity DROP COLUMN one');
        $this->dao->exec('ALTER TABLE simpleEntity ADD COLUMN one VARCHAR(11)');

        // Act
        $results = $this->schemaTool->update(true);

        // Assert
        $this->assertTrue($this->tableExists('simpleEntity'));
        $this->assertEquals('updated', $results['simpleEntity']['status']);
        $this->assertEquals('updated', $results['simpleEntity']['columns']['one']['status']);
    }

    public function testUpdateUpdatesAColumnIfTypeIsDecimalAndPrecisionIsDifferent()
    {
        // Arrange
        $this->schemaTool->create();
        $this->dao->exec('ALTER TABLE simpleEntity DROP COLUMN decimal2');
        $this->dao->exec('ALTER TABLE simpleEntity ADD COLUMN decimal2 DECIMAL(3, 1)');

        // Act
        $results = $this->schemaTool->update(true);

        // Assert
        $this->assertTrue($this->tableExists('simpleEntity'));
        $this->assertEquals('updated', $results['simpleEntity']['status']);
        $this->assertEquals('unchanged', $results['simpleEntity']['columns']['decimal1']['status']);
        $this->assertEquals('updated', $results['simpleEntity']['columns']['decimal2']['status']);
    }

    public function testUpdateUpdatesANonNullableColumnIfChangedToNullable()
    {
        // Arrange
        $this->schemaTool->create();
        $this->dao->exec('ALTER TABLE simpleEntity DROP COLUMN one');
        $this->dao->exec('ALTER TABLE simpleEntity ADD COLUMN one INT(11)');

        // Act
        $results = $this->schemaTool->update(true);

        // Assert
        $this->assertTrue($this->tableExists('simpleEntity'));
        $this->assertEquals('updated', $results['simpleEntity']['status']);
        $this->assertEquals('updated', $results['simpleEntity']['columns']['one']['status']);
    }

    public function testUpdateUpdatesANullableColumnIfChangedToNonNullable()
    {
        // Arrange
        $this->schemaTool->create();
        $this->dao->exec('ALTER TABLE simpleEntity DROP COLUMN bool1');
        $this->dao->exec('ALTER TABLE simpleEntity ADD COLUMN bool1 INT(11) NOT NULL');

        // Act
        $results = $this->schemaTool->update(true);

        // Assert
        $this->assertTrue($this->tableExists('simpleEntity'));
        $this->assertEquals('updated', $results['simpleEntity']['status']);
        $this->assertEquals('updated', $results['simpleEntity']['columns']['bool1']['status']);
    }
}