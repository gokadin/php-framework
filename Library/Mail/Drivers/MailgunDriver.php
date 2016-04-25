<?php

namespace Library\Mail\Drivers;

use Mailgun\Mailgun;
use Mailgun\Messages\MessageBuilder;

class MailgunDriver
{
    /**
     * @var Mailgun
     */
    private $mailgun;

    /**
     * @var string
     */
    private $domain;

    /**
     * @var MessageBuilder
     */
    private $builder;

    public function __construct($config)
    {
        $this->mailgun = new Mailgun($config['secret']);
        $this->domain = $config['domain'];
    }

    public function prepare()
    {
        $this->builder = $this->mailgun->MessageBuilder();
    }

    public function send()
    {
        $this->builder->setDkim(true);
        $this->mailgun->post($this->domain.'/messages', $this->builder->getMessage(), $this->builder->getFiles());
    }

    public function setFrom($from, $name = '')
    {
        $this->builder->setFromAddress($from, ['full_name' => $name]);
    }

    public function setTo($to, $name = '')
    {
        $this->builder->addToRecipient($to, ['full_name' => $name]);
    }

    public function setSubject($subject)
    {
        $this->builder->setSubject($subject);
    }

    public function setHtmlBody($html)
    {
        $this->builder->setHtmlBody($html);
    }

    public function setTextBody($text)
    {
        $this->builder->setTextBody($text);
    }
}