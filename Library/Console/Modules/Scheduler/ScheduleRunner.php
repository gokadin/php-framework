<?php

namespace Library\Console\Modules\Scheduler;

use Carbon\Carbon;
use Config\SchedulerConfig;
use Library\Scheduler\Scheduler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScheduleRunner extends Command
{
    protected function configure()
    {
        $this
            ->setName('scheduler:run')
            ->setDescription('Runs the scheduler once.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schedulerConfig = new SchedulerConfig();
        $scheduler = new Scheduler();
        $schedulerConfig->run($scheduler);

        foreach ($scheduler->dueEvents() as $event)
        {
            if (!$event->run())
            {
                $output->writeln('<error>Error while running event '.$event->name().' at '.Carbon::now().'</error>');
                continue;
            }

            $output->writeln('<info>Event '.$event->name().' processed at '.Carbon::now().'</info>');
        }
    }
}