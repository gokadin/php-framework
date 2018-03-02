<?php

namespace Tests\Library\Authentication;

use Library\DataMapper\Database\SchemaTool;
use Library\DataMapper\DataMapper;
use PDO;
use Tests\App\Models\Admin;
use Tests\BaseTest;

abstract class AuthenticationBaseTest extends BaseTest
{
    protected $dao;

    protected $schemaTool;

    /**
     * @var DataMapper
     */
    protected $dm;

    private function setUpBase(array $classes)
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

            'classes' => $classes
        ];

        $this->schemaTool = new SchemaTool($config);
        $this->schemaTool->create();

        $this->dao = new PDO('mysql:host='.$config['mysql']['host'].';dbname='.$config['mysql']['database'],
            $config['mysql']['username'],
            $config['mysql']['password']);

        $this->dao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->dm = new DataMapper($config);
    }

    protected function setUpAdmin()
    {
        $this->setUpBase([
            Admin::class
        ]);
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->schemaTool->drop();

        $this->dao = null;

        $this->dm->disconnect();
    }
}