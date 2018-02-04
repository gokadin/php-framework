<?php
namespace Library\Pyramid\Design;

class CommandProcessor
{
    /**
     * @var Builder
     */
    private $builder;

    public function __construct()
    {
        $this->builder = new Builder();
    }

    public function process(string $text)
    {
        if ($text == 'a') {
            return $this->builder->addHeader();
        }

        if (strpos($text, 'set title') !== false) {
            return $this->builder->setTitle(substr($text, strpos($text, 'title') + 6));
        }

        return 'Command not found.';
    }
}