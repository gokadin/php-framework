<?php

namespace Library\IscClient\Modules;

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
        $isc = new IscClient($this->readConfig());
    }

    private function readConfig()
    {
        $config = yaml_parse_file($this->basePath.'/'.self::ISC_CONFIG_FILE);


        $isc = new IscClient($config);
    }
}