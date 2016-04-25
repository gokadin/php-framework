<?php

namespace Library\Container;

use Library\Configuration\Config;
use Library\Database\ModelFactory;
use Library\Events\EventManager;
use Library\Http\Redirect;
use Library\Http\Request;
use Library\Http\Response;
use Library\Http\View;
use Library\Log\Log;
use Library\Mail\Mail;
use Library\Queue\Queue;
use Library\Routing\Router;
use Library\Database\Database;
use Library\DataMapper\DataMapper;
use Library\Http\Form;
use Library\Session\Session;
use Library\Shao\Shao;
use Library\Transformer\Transformer;
use Library\Validation\Validator;

class ContainerConfiguration
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function configureContainer()
    {
        $app = $this->container->resolveInstance('app');

        $this->container->registerInstance('config', new Config());
        $this->container->registerInstance('request', new Request());
        $this->container->registerInstance('view', new View());
        $session = new Session();
        $this->container->registerInstance('session', $session);
        $this->container->registerInstance('log', new Log());
        $queueConfig = require $app->basePath().'Config/queue.php';
        $queue = new Queue($queueConfig);
        $this->container->registerInstance('queue', $queue);
        $eventManagerConfig = require $app->basePath().'Config/events.php';
        $this->container->registerInstance('eventManager',
            new EventManager($eventManagerConfig, $this->container, $queue));
        $transformerConfig = require $app->basePath().'Config/transformations.php';
        $this->container->registerInstance('transformer', new Transformer($transformerConfig));

        // ORM
        $datamapperConfig = require $app->basePath().'Config/datamapper.php';
        $dm = new DataMapper($datamapperConfig);
        $this->container->registerInstance('datamapper', $dm);

        // database
        $databaseConfig = require $app->basePath().'Config/database.php';
        $database = new Database($databaseConfig);
        $this->container->registerInstance('database', $database);

        // Services requiring a database
        $validator = new Validator($database);
        $this->container->registerInstance('validator', $validator);
        $router = new Router($this->container, $validator);
        $this->container->registerInstance('router', $router);
        $this->container->registerInstance('response', new Response($router, $session));
        $shao = new Shao($this->container);
        $this->container->registerInstance('shao', $shao);
        $mailConfig = require $app->basePath().'Config/mail.php';
        $this->container->registerInstance('mail', new Mail($mailConfig, $shao));
    }
}