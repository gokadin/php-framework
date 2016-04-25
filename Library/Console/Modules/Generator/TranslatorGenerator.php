<?php

namespace Library\Console\Modules\Generator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TranslatorGenerator extends Command
{
    protected function configure()
    {
        $this
            ->setName('make:translator')
            ->setDescription('Generates a translator.')
            ->addArgument('path')
            ->addOption('authenticated');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $templates = new Templates();
        $root = __DIR__.'/../../../../App/Http/Translators/';
        $fullPath = $root.$input->getArgument('path').'.php';

        file_put_contents($fullPath, $templates->generateTranslator(
            $input->getArgument('path', $input->getOption('authenticated'))));

        $output->writeln('<info>Translator generated</info>');
    }
}