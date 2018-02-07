<?php

namespace Library\DataMapper\Console\Modules;

use Library\DataMapper\Database\SchemaTool;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateSchema extends Command
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
            ->setName('schema:create')
            ->setDescription('Create schema.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schemaTool = new SchemaTool($this->config);
        $results = $schemaTool->create();

        $successes = 0;
        $failures = 0;
        foreach ($results as $table => $success)
        {
            if ($success)
            {
                $output->writeln('<info>--> Created table '.$table.'.</info>');
                $successes++;
                continue;
            }

            $output->writeln('<error>--> Could not create table '.$table.'.</error>');
            $failures++;
        }

        $output->writeln('');
        $output->writeln('-------------------------------');
        $output->writeln('<info>Created '.$successes.' tables.</info>');
        $output->writeln('<info>Skipped '.$failures.' tables.</info>');
    }
}