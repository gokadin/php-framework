<?php

namespace Library\IscClient;

abstract class IscEntity
{
    public const ISC_EVENT = 'ISC_EVENT';
    public const ISC_COMMAND = 'ISC_COMMAND';
    public const ISC_QUERY = 'ISC_QUERY';

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $message;

    /**
     * IscEntity constructor.
     *
     * @param string $type
     * @param string $message
     */
    public function __construct(string $type, string $message)
    {
        $this->type = $type;
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return $this->message;
    }
}