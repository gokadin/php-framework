<?php

namespace Library\DataMapper\Console\Modules;

use Library\DataMapper\Database\SchemaTool;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DropSchema extends Command
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
            ->setName('schema:drop')
            ->setDescription('Drop schema.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schemaTool = new SchemaTool($this->config);
        $schemaTool->drop();

        $output->writeln('<info>Schema dropped.</info>');
    }
}