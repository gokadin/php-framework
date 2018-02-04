<?php

namespace Library\DataMapper\Console\Modules;

use Faker\Factory;
use Library\DataMapper\Database\SchemaTool;
use Library\DataMapper\DataMapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class SeedDatabase extends Command
{
    protected $config;

    public function __construct($config)
    {
        parent::__construct();

        $this->config = $config;
    }

    protected function configure()
    {
        $this
            ->setName('db:seed')
            ->setDescription('Seed the database.')
            ->addOption(
                'reset',
                null,
                InputOption::VALUE_NONE,
                'Erases all data from the database before seeding.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schemaTool = new SchemaTool($this->config);
        $dm = new DataMapper($this->config);

        if ($input->getOption('reset'))
        {
            $schemaTool->drop();
            $schemaTool->create();
            $output->writeln('<info>Erased database.</info>');
        }

        $dbSeeder = new \Config\Datamapper\Seeds($dm, Factory::create());

        $dbSeeder->run();

        $output->writeln('<info>Tables seeded.</info>');
    }
}