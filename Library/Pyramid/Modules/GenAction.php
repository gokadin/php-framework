<?php

namespace Library\Pyramid\Modules;

use Library\Pyramid\Generators\Client\Action\ActionGenerator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenAction extends CommandBase
{
    public function configure()
    {
        $this
            ->setName('gen:action')
            ->setDescription('Generates an action.')
            ->addArgument('type')
            ->addArgument('name');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $generator = new ActionGenerator($this->config);

        $generator->generate($input->getArgument('type'), $input->getArgument('name'));

        $output->writeln('<info>Action generated.</info>');
    }
}