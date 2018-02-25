<?php

namespace Library\Console\Modules\Generator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ControllerGenerator extends Command
{
    protected function configure()
    {
        $this
            ->setName('make:controller')
            ->setDescription('Generates a controller.')
            ->addArgument('path')
            ->addOption('api');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $templates = new Templates();
        $root = __DIR__.'/../../../../App/Http/Controllers/';
        $fullPath = $root.$input->getArgument('path').'.php';

        file_put_contents($fullPath, $templates->generateController(
            $input->getArgument('path'), $input->getOption('api')));

        $output->writeln('<info>Controller generated</info>');
    }
}