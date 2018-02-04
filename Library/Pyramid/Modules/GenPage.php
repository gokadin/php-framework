<?php

namespace Library\Pyramid\Modules;

use Library\Pyramid\Generators\Client\Page\PageGenerator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenPage extends CommandBase
{
    public function configure()
    {
        $this
            ->setName('gen:page')
            ->setDescription('Generates a page.')
            ->addArgument('pageName');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $generator = new PageGenerator($this->config);

        $generator->generate($input->getArgument('pageName'));

        $output->writeln('<info>Page generated.</info>');
    }
}