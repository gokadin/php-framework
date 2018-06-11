<?php

namespace Library\Engine\Console\Modules;

use Library\Engine\Schema\SchemaSynchronizer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SynchronizeSchema extends Command
{
    const SCHEMA_FILE = '/Config/Schema/schema.php';
    const PREVIOUS_SCHEMA_FILE = '/Storage/Framework/previousSchema.json';
    const DATAMAPPER_SCRIPT_FILE = '/Config/FeaturesConfig/datamapper.php';
    const ENGINE_CONFIG_FILE = '/Config/FeaturesConfig/engine.php';

    /**
     * @var string
     */
    private $basePath;

    public function __construct(string $basePath)
    {
        parent::__construct();

        $this->basePath = $basePath;
    }

    protected function configure()
    {
        $this
            ->setName('schema:sync')
            ->setDescription('Synchronize schema.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $engineConfig = require $this->basePath.self::ENGINE_CONFIG_FILE;
        $synchronizer = new SchemaSynchronizer($this->basePath, $engineConfig, $this->basePath.self::DATAMAPPER_SCRIPT_FILE);

        $schema = require $this->basePath.self::SCHEMA_FILE;
        $previousSchema = json_decode(file_get_contents($this->basePath.self::PREVIOUS_SCHEMA_FILE), true);
        $result = $synchronizer->synchronize($schema, $previousSchema);

        if ($result['success'])
        {
            file_put_contents($this->basePath.self::PREVIOUS_SCHEMA_FILE, json_encode($schema, JSON_PRETTY_PRINT));

            exec('php '.$this->basePath.self::DATAMAPPER_SCRIPT_FILE.' schema:update --force 2>&1', $dataMapperOutput);
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