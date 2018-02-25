<?php

namespace Library\DataMapper\Console\Modules;

use Library\DataMapper\Database\SchemaTool;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateSchema extends Command
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
            ->setName('schema:update')
            ->setDescription('Update schema.')
            ->addOption(
                'force',
                null,
                InputOption::VALUE_NONE,
                'Will match the configured schema exactly, dropping tables and columns as necessary.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schemaTool = new SchemaTool($this->config);
        $results = $input->getOption('force')
            ? $schemaTool->update(true)
            : $schemaTool->update();

        $tablesCreated = 0;
        $tablesDropped = 0;
        $tablesUpdated = 0;
        $tablesUnchanged = 0;
        foreach ($results as $table => $result)
        {
            switch ($result['status'])
            {
                case 'unchanged':
                    $tablesUnchanged++;
                    break;
                case 'created':
                    $tablesCreated++;
                    $output->writeln('<info>--> Created table '.$table.'.</info>');
                    break;
                case 'dropped':
                    $tablesDropped++;
                    $output->writeln('<info>--> Dropped table '.$table.'.</info>');
                    break;
                case 'updated':
                    $tablesUpdated++;
                    $output->writeln('<info>--> Updated table '.$table.':</info>');
                    foreach ($result['columns'] as $column => $columnResults)
                    {
                        switch ($columnResults['status'])
                        {
                            case 'created':
                                $output->writeln('<info>    --> Created column '.$column.'.</info>');
                                break;
                            case 'dropped':
                                $output->writeln('<info>    --> Dropped column '.$column.'.</info>');
                                break;
                            case 'updated':
                                $output->writeln('<info>    --> Updated column '.$column.'.</info>');
                                break;
                        }
                    }

                    break;
            }

            $output->writeln('');
        }

        $output->writeln('-------------------------------');
        $output->writeln('<info>'.$tablesCreated.' tables created.</info>');
        $output->writeln('<info>'.$tablesDropped.' tables dropped.</info>');
        $output->writeln('<info>'.$tablesUpdated.' tables updated.</info>');
        $output->writeln('<info>'.$tablesUnchanged.' tables unchanged.</info>');
    }
}