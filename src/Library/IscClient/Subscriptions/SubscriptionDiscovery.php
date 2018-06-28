<?php

namespace Library\IscClient\Subscriptions;

use Library\IscClient\IscConstants;

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
        }
        else if (strpos($rootFile, 'Events') !== false)
        {
            $this->parseEventFile($rootFile);
        }
    }

    private function parseEventFile(string $file)
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
            if (!$method->isPublic() || substr($method->getName(), 0, 2) != IscConstants::EVENT_METHOD_PREFIX)
            {
                continue;
            }

            $action = lcfirst(substr($method->getName(), 2));
            $this->subscriptionRoutes[$topicName][IscConstants::EVENT_TYPE][$action] = [
                'class' => $class,
                'method' => $method->getName(),
                'topic' => $topicName,
                'type' => IscConstants::EVENT_TYPE,
                'action' => $action
            ];
        }
    }
}