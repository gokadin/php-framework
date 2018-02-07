<?php

namespace Library\Console\Modules\Queue;

use Library\Database\Database;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;

class QueueListener extends Command
{
    const SLEEP_DURATION_SEC = 2;
    const MAX_ATTEMPTS = 3;

    protected $database;
    protected $queueTable;
    protected $failedTable;

    public function __construct(Database $database)
    {
        parent::__construct();

        $this->database = $database;

        $settings = require App::basePath().'Config/queue.php';
        $this->queueTable = $settings['connections']['database']['table'];
        $this->failedTable = $settings['connections']['database']['failedTable'];
    }

    protected function configure()
    {
        $this
            ->setName('queue:listen')
            ->setDescription('Listen to a queue.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $attempts = 0;
        while (true)
        {
            list($id, $maxAttempts, $job, $handler, $type) = $this->getNextJobArray();

            if (is_null($job))
            {
                $output->writeln('<comment>Waiting for new job...</comment>');
                sleep(self::SLEEP_DURATION_SEC);
                continue;
            }

            try
            {
                switch ($type)
                {
                    case 'job':
                        $this->executeJob($job);
                        break;
                    case 'eventListener':
                        $this->executeEventListener($job, $handler);
                        break;
                }

                $attempts = 0;
                $this->removeJob($id);
            }
            catch (Exception $e)
            {
                $attempts++;
                $output->writeln('<error>Failed processing job #'.$id.' : '.
                    get_class($job).' (attempt '.$attempts.').</error>');

                Log::error('Failed processing job #'.$id.' : '.
                    get_class($job).' (attempt '.$attempts.'), message: '.$e->getMessage());

                if ($attempts >= $maxAttempts)
                {
                    $this->handleFailedJob($job);
                    $this->removeJob($id);
                    $attempts = 0;
                }
            }

            $output->writeln('<info>Processed job #'.$id.' : '.get_class($job).'.</info>');
            sleep(self::SLEEP_DURATION_SEC);
        }
    }

    protected function executeJob($job)
    {
        $parameters = App::container()->resolveMethodParameters($job, 'handle');
        call_user_func_array([$job, 'handle'], $parameters);
    }

    protected function executeEventListener($job, $handler)
    {
        $handler->handle($job);
    }

    protected function getNextJobArray()
    {
        $query = $this->database->prepare('SELECT * FROM '.$this->queueTable.' '.
            'WHERE execution_date <= NOW()'.
            'ORDER BY execution_date ASC LIMIT 1');

        $query->execute();

        if ($row = $query->fetch())
        {
            $job = unserialize($row['job']);
            if ($job == null)
            {
                $this->removeJob($row['id']);
                return null;
            }

            $handler = null;
            if ($row['type'] == 'eventListener')
            {
                $handler = unserialize($row['handler']);
                if ($handler == null)
                {
                    $this->removeJob($row['id']);
                    return null;
                }
            }

            return [
                $row['id'],
                $row['max_attempts'],
                $job,
                $handler,
                $row['type']
            ];
        }

        return null;
    }

    protected function removeJob($id)
    {
        $this->database->exec('DELETE FROM '.$this->queueTable.' WHERE id='.$id);
    }

    protected function handleFailedJob($job, $handler)
    {
        if ($handler != null)
        {
            $this->database->exec('INSERT INTO '.$this->failedTable.' (job, handler) VALUES('.
                '\''.str_replace('\\', '\\\\', serialize($job)).'\', '.
                '\''.str_replace('\\', '\\\\', serialize($handler)).'\')');
            return;
        }

        $this->database->exec('INSERT INTO '.$this->failedTable.' (job, handler) VALUES('.
            '\''.str_replace('\\', '\\\\', serialize($job)).'\', \'undefined\')');
    }
}