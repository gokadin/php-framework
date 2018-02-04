<?php

namespace Library\Pyramid\Modules;

use Library\Pyramid\Generators\Client\Component\ComponentGenerator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenComponent extends CommandBase
{
    public function configure()
    {
        $this
            ->setName('gen:component')
            ->setDescription('Generates a component.')
            ->addArgument('componentName');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $generator = new ComponentGenerator($this->config);

        $generator->generate($input->getArgument('componentName'));

        $output->writeln('<info>Component generated.</info>');
    }
}