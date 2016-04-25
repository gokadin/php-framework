<?php

namespace Library\Console\Modules\Generator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RequestGenerator extends Command
{
    protected function configure()
    {
        $this
            ->setName('make:request')
            ->setDescription('Generates a request.')
            ->addArgument('path')
            ->addOption('authenticated');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $templates = new Templates();
        $root = __DIR__.'/../../../../App/Http/Requests/';
        $fullPath = $root.$input->getArgument('path').'.php';

        file_put_contents($fullPath, $templates->generateRequest(
            $input->getArgument('path'), $input->getOption('authenticated')));

        $output->writeln('<info>Request generated</info>');
    }
}