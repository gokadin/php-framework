<?php

namespace Library\Pyramid\Modules;

use Library\Pyramid\Generators\Client\Reducer\ReducerGenerator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenReducer extends CommandBase
{
    public function configure()
    {
        $this
            ->setName('gen:reducer')
            ->setDescription('Generates a reducer.')
            ->addArgument('name');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $generator = new ReducerGenerator($this->config);

        $generator->generate($input->getArgument('name'));

        $output->writeln('<info>Reducer generated.</info>');
    }
}