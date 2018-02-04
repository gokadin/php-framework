<?php

namespace Library\Pyramid\Modules;

use Library\Pyramid\Design\CommandProcessor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Design extends CommandBase
{
    public function configure()
    {
        $this
            ->setName('design')
            ->setDescription('Enter design mode.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $commandProcessor = new CommandProcessor();
        $run = true;
        $in = fopen("php://stdin","r");

        while ($run)
        {
            $output->write('> ');

            $text = trim(fgets($in));

            if ($text == 'exit')
            {
                $run = false;
                continue;
            }

            echo $commandProcessor->process($text).PHP_EOL;
        }

        fclose($in);
    }
}