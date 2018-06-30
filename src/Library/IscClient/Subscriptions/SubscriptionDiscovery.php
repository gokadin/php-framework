<?php

namespace Library\IscClient\Subscriptions;

use Library\IscClient\IscConstants;
use Library\IscClient\IscException;

class SubscriptionDiscovery
{
    /**
     * @var string
     */
    private $basePath;

    /**
     * @var string
     */
    private $iscRoot;

    /**
     * @var array
     */
    private $subscriptionRoutes;

    /**
     * @var bool
     */
    private $subscriptionRoutesLoaded;

    /**
     * SubscriptionDiscovery constructor.
     *
     * @param string $basePath
     * @param string $iscRoot
     */
    public function __construct(string $basePath, string $iscRoot)
    {
        $this->basePath = $basePath;
        $this->iscRoot = $iscRoot;

        $this->subscriptionRoutes = [];
        $this->subscriptionRoutesLoaded = false;
    }

    public function findSubscriptionRoute(string $topic, string $type, string $name)
    {
        if (!$this->subscriptionRoutesLoaded)
        {
            $this->loadSubscriptionRoutes();
        }

        if (!isset($this->subscriptionRoutes[$topic]) ||
            !isset($this->subscriptionRoutes[$topic][$type]) ||
            !isset($this->subscriptionRoutes[$topic][$type][$name]))
        {
            return null;
        }

        return $this->subscriptionRoutes[$topic][$type][$name];
    }

    public function getSubscriptionStrings()
    {
        if (!$this->subscriptionRoutesLoaded)
        {
            $this->loadSubscriptionRoutes();
        }

        $subscriptionStrings = [];
        foreach ($this->subscriptionRoutes as $topic => $types)
        {
            foreach ($types as $type => $routes)
            {
                foreach ($routes as $action => $route)
                {
                    $subscriptionStrings[] = implode('.', [$topic, $type, $action]);
                }
            }
        }

        return $subscriptionStrings;
    }

    private function loadSubscriptionRoutes()
    {
        $this->parseFiles($this->basePath.'/'.$this->iscRoot);

        $this->subscriptionRoutesLoaded = true;
    }

    private function parseFiles(string $rootFile)
    {
        if (is_dir($rootFile))
        {
            foreach (scandir($rootFile) as $file)
            {
                if ($file == '..' || $file == '.')
                {
                    continue;
                }

                $this->parseFiles($rootFile.'/'.$file);
            }

            return;
        }

        if (strpos($rootFile, 'Events') !== false)
        {
            $this->parseControllerFile($rootFile, IscConstants::EVENT_TYPE);

            return;
        }

        if (strpos($rootFile, 'Commands') !== false)
        {
            $this->parseControllerFile($rootFile, IscConstants::COMMAND_TYPE);

            return;
        }

        if (strpos($rootFile, 'Queries') !== false)
        {
            $this->parseControllerFile($rootFile, IscConstants::QUERY_TYPE);
        }
    }

    private function parseControllerFile(string $file, string $type)
    {
        $topicName = str_replace($this->basePath.'/'.$this->iscRoot.'/', '', $file);
        $topicName = substr($topicName, 0, strrpos($topicName, '/'));
        $topicName = str_replace('/', '.', $topicName);

        $class = str_replace('.php', '', str_replace('/', '\\', str_replace($this->basePath.'/', '', $file)));
        if (getenv('APP_ENV') == 'test')
        {
            $class = 'Tests\\'.$class;
        }
        $r = new \ReflectionClass($class);
        foreach ($r->getMethods() as $method)
        {
            $methodPrefix = $this->getMethodPrefix($type);
            if (!$method->isPublic() || !$this->isMethodAnAction($method, $methodPrefix))
            {
                continue;
            }

            $action = lcfirst(substr($method->getName(), strlen($methodPrefix)));
            $route = new SubscriptionRoute($class, $method->getName(), $topicName, $type, $action);
            $this->subscriptionRoutes[$topicName][$type][$action] = $route;
            fwrite(STDOUT, 'Registered route '.$route->action().PHP_EOL);
        }
    }

    private function getMethodPrefix(string $type): string
    {
        switch ($type)
        {
            case IscConstants::EVENT_TYPE:
                return IscConstants::EVENT_METHOD_PREFIX;
            case IscConstants::COMMAND_TYPE:
                return IscConstants::COMMAND_METHOD_PREFIX;
            case IscConstants::QUERY_TYPE:
                return IscConstants::QUERY_METHOD_PREFIX;
            default:
                throw new IscException('Unkown action type '.$type.'.');
        }
    }

    private function isMethodAnAction(\ReflectionMethod $method, string $methodPrefix): bool
    {
        return substr($method->getName(), 0, strlen($methodPrefix)) == $methodPrefix;
    }
}