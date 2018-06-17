<?php

namespace Library\Core;

class Environment
{
    public const APP_ENV_KEY = 'APP_ENV';

    /**
     * @var string
     */
    private $basePath;

    /**
     * EnvironmentLoader constructor.
     *
     * @param string $basePath
     */
    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * Loads environment variables from .env and .env.<environment> files
     */
    public function load()
    {
        $this->putEnvironmentVariables($this->parseEnvironmentValue());
    }

    /**
     * @return string
     * @throws CoreException
     */
    private function parseEnvironmentValue(): string
    {
        $content = fopen($this->basePath.'/.env', 'r');
        if (!$content)
        {
            throw new CoreException('.env file not found.');
        }

        $line = fgets($content);
        fclose($content);
        if (preg_match('/'.self::APP_ENV_KEY.'\=[.a-zA-Z0-9_\-@]+[\r]?[\n]?$/', $line) != 1)
        {
            throw new CoreException('.env file was not set up correctly.');
        }

        return explode('=', trim(str_replace(PHP_EOL, '', $line)))[1];
    }

    /**
     * @param string $environmentValue
     * @throws CoreException
     */
    private function putEnvironmentVariables(string $environmentValue): void
    {
        putenv(self::APP_ENV_KEY.'='.$environmentValue);

        $environmentFileName = '.env.'.$environmentValue;
        $content = fopen($this->basePath.'/'.$environmentFileName, 'r');
        if (!$content)
        {
            throw new CoreException($environmentFileName.' file not found.');
        }

        while (($line = fgets($content)) !== false) {
            if (preg_match('/[a-zA-Z0-9_-]+\=[.a-zA-Z0-9_\-@\/\:]+[\r]?[\n]?$/', $line) != 1)
            {
                continue;
            }

            list($key, $value) = explode('=', trim(str_replace(PHP_EOL, '', $line)));
            if (strtoupper($value) == 'FALSE')
            {
                $value = 0;
            }
            putenv($key.'='.$value);
        }

        fclose($content);
    }
}