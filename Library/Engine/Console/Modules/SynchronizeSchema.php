<?php

namespace Library\Engine\Console\Modules;

use Library\Engine\Schema\SchemaSynchronizer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SynchronizeSchema extends Command
{
    const PREVIOUS_SCHEMA_FILE = __DIR__.'/../../../../Storage/Framework/previousSchema.php';
    const DATAMAPPER_SCRIPT_FILE = __DIR__.'/../../../../datamapper';

    private $schema;

    public function __construct($schema)
    {
        parent::__construct();

        $this->schema = $schema;
    }

    protected function configure()
    {
        $this
            ->setName('schema:sync')
            ->setDescription('Synchronize schema.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $previousSchema = [];
        if (file_exists(self::PREVIOUS_SCHEMA_FILE))
        {
            $previousSchema = json_decode(file_get_contents(self::PREVIOUS_SCHEMA_FILE), true);
        }

        $synchronizer = new SchemaSynchronizer($this->schema, $previousSchema);
        $result = $synchronizer->synchronize();

        if ($result['success'])
        {
            file_put_contents(self::PREVIOUS_SCHEMA_FILE, json_encode($this->schema, JSON_PRETTY_PRINT));

            echo self::DATAMAPPER_SCRIPT_FILE;
            exec('php '.self::DATAMAPPER_SCRIPT_FILE.' schema:update --force 2>&1', $dataMapperOutput);
            foreach ($dataMapperOutput as $dataMapperLine)
            {
                $output->writeln('<info>'.$dataMapperLine.'</info>');
            }

            echo PHP_EOL;
            $output->writeln('<info>Sync successfull.</info>');
            return;
        }

        $output->writeln('<error>Error synchronizing schema: '.$result['message'].'</error>');
    }
}