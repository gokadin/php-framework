<?php

namespace Library\Mail;

use Library\Http\View;
use Library\Mail\Drivers\MailgunDriver;

class Mail
{
    /**
     * @var mixed
     */
    private $driver;

    public function __construct($config)
    {
        $this->initializeDriver($config);
    }

    private function initializeDriver($config)
    {
        switch ($config['driver'])
        {
            default:
                $this->driver = new MailgunDriver($config['mailgun']);
                break;
        }
    }

    public function send($route, array $data, Callable $callback)
    {
        $this->prepareHtmlMessage($route, $data, $callback);

        $this->executeCallback($callback);

        $this->sendMessage();
    }

    public function sendPlainText($text, Callable $callback)
    {
        $this->prepareTextMessage($text);

        $this->executeCallback($callback);

        $this->sendMessage();
    }

    public function to($to, $name = '')
    {
        $this->driver->setTo($to, $name);
    }

    public function from($from, $name = '')
    {
        $this->driver->setFrom($from, $name);
    }

    public function subject($subject)
    {
        $this->driver->setSubject($subject);
    }

    private function prepareTextMessage($text)
    {
        $this->driver->prepare();

        $this->driver->setTextBody($text);
    }

    private function prepareHtmlMessage($route, array $data)
    {
        $this->driver->prepare();

        $view = $_SERVER['DOCUMENT_ROOT'].'/../'.View::VIEW_FOLDER.'/'.str_replace('.', '/', $route);
        $file = $this->getFile($view);

        extract($data);

        ob_start();
        require $file;
        $content = ob_get_clean();

        $this->driver->setHtmlBody($content);
    }

    private function executeCallback(Callable $callback)
    {
        $callback($this);
    }

    private function getFile($view)
    {
        $validExtensions = ['.php', '.html'];

        foreach ($validExtensions as $validExtension)
        {
            if (file_exists($view.$validExtension))
            {
                return $view.$validExtension;
            }
        }

        throw new MailException('File '.$view.' does not exist.');
    }

    private function sendMessage()
    {
        $this->driver->send();
    }
}