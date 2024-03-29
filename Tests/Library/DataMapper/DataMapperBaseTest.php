<?php

namespace Tests\Library\DataMapper;

use Tests\TestData\DataMapper\LazyEntityOne;
use Tests\TestData\DataMapper\LazyEntityTwo;
use Tests\TestData\DataMapper\TimeEntity;
use Tests\TestData\DataMapper\Event;
use Tests\TestData\DataMapper\Lesson;
use Library\DataMapper\DataMapper;
use Tests\BaseTest;
use Tests\TestData\DataMapper\Address;
use Tests\TestData\DataMapper\AddressTwo;
use Tests\TestData\DataMapper\SimpleEntity;
use Library\DataMapper\Database\SchemaTool;
use PDO;
use Tests\TestData\DataMapper\Teacher;
use Tests\TestData\DataMapper\Student;

abstract class DataMapperBaseTest extends BaseTest
{
    protected $schemaTool;

    /**
     * @var PDO
     */
    protected $dao;

    /**
     * @var DataMapper
     */
    protected $dm;

    /**
     * @var array
     */
    protected $classes;

    protected function setUpBase()
    {
        date_default_timezone_set('America/Montreal');

        $this->loadEnvironment();

        $config = [
            'mappingDriver' => 'annotation',

            'databaseDriver' => 'mysql',

            'mysql' => [
                'host' => getenv('DATABASE_HOST'),
                'database' => getenv('DATABASE_NAME'),
                'username' => getenv('DATABASE_USERNAME'),
                'password' => getenv('DATABASE_PASSWORD')
            ],

            'classes' => $this->classes
        ];

        $this->schemaTool = new SchemaTool($config);
        $this->schemaTool->create();

        $this->dao = new PDO('mysql:host='.$config['mysql']['host'].';dbname='.$config['mysql']['database'],
            $config['mysql']['username'],
            $config['mysql']['password']);

        $this->dao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->dm = new DataMapper($config);
    }

    protected function setUpSimpleEntity()
    {
        $this->classes = [
            SimpleEntity::class
        ];

        $this->setUpBase();
    }

    protected function setUpTimeEntity()
    {
        $this->classes = [
            TimeEntity::class
        ];

        $this->setUpBase();
    }

    protected function setUpLazyEntities()
    {
        $this->classes = [
            LazyEntityOne::class,
            LazyEntityTwo::class
        ];

        $this->setUpBase();
    }

    public function setUpAssociations()
    {
        $this->classes = [
            Teacher::class,
            Student::class,
            Address::class,
            AddressTwo::class,
            Lesson::class,
            Event::class
        ];

        $this->setUpBase();
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->schemaTool->drop();

        $this->dao = null;

        $this->dm->disconnect();
    }
}