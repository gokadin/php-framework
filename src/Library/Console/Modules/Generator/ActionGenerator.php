<?php

namespace Library\Console\Modules\Generator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ActionGenerator extends Command
{
    const tab = '    ';

    protected function configure()
    {
        $this
            ->setName('make:action')
            ->setDescription('Generates an action.')
            ->addArgument('path')
            ->addOption('authenticated');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $templates = new Templates();
        $root = __DIR__.'/../../../../App/Http/';

        $requestPath = $input->getArgument('path').'Request';
        $requestFullPath = $root.$requestPath.'.php';
        file_put_contents($requestFullPath, $templates->generateRequest(
            $requestPath, $input->getOption('authenticated')));

        $translationPath = $input->getArgument('path').'Translation';
        $translationFullPath = $root.$translationPath.'.php';
        file_put_contents($translationFullPath, $templates->generateTranslator(
            $translationPath, $input->getOption('authenticated')));

        $output->writeln('<info>Action generated</info>');
    }
}