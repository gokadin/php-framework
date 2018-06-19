<?php

namespace Library\IscClient\Console\Modules;

use Library\IscClient\IscClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IscClientRunner extends Command
{
    private const ISC_CONFIG_FILE = '/Config/FeaturesConfig/isc.yml';

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
            ->setName('isc:run')
            ->setDescription('Run the ISC client.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $isc = new IscClient($this->basePath, $this->readConfig());

        $isc->run();
    }

    private function readConfig()
    {
        $configFile = $this->basePath.'/'.self::ISC_CONFIG_FILE;
        if (!file_exists($configFile))
        {
            return [];
        }

        $config = yaml_parse_file($configFile);
        return is_null($config) ? [] : $config;
    }
}