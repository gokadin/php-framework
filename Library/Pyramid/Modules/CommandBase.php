<?php

namespace Library\Pyramid\Modules;

use Library\Pyramid\Config\ConfigReader;
use Symfony\Component\Console\Command\Command;

class CommandBase extends Command
{
    /**
     * @var array
     */
    protected $config;

    public function __construct()
    {
        parent::__construct();

        $configReader = new ConfigReader();

        $this->config = $configReader->read();
    }
}