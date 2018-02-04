<?php

namespace Library\Http;

use Library\Shao\Shao;
use Symfony\Component\Yaml\Exception\RuntimeException;

class View
{
    const VIEW_FOLDER = 'resources/views';

    protected $basePath;
    protected $content;
    protected $vars;
    private $viewAction;

    public function __construct($viewAction = null, array $data = [])
    {
        $this->basePath = __DIR__.'/../../'.self::VIEW_FOLDER;

        if (!is_null($viewAction))
        {
            $this->add($data);
            $this->viewAction = $viewAction;
        }
    }

    public function make($viewAction, array $data = [])
    {
        $this->add($data);
        $this->viewAction = $viewAction;

        return $this;
    }

    public function add(array $data)
    {
        foreach ($data as $key => $value)
        {
            $this->vars[$key] = $value;
        }
    }

    public function processView()
    {
        $view = $this->basePath.'/'.str_replace('.', '/', $this->viewAction);

        $contentFile = $this->getContentFile($view);

        if (!is_null($this->vars))
        {
            extract($this->vars);
        }

        ob_start();
        require $contentFile;
        return ob_get_clean();
    }

    protected function getContentFile($view)
    {
        $validExtensions = ['.php', '.html'];

        foreach ($validExtensions as $validExtension)
        {
            if (file_exists($view.$validExtension))
            {
                return $view.$validExtension;
            }
        }

        throw new RuntimeException('File '.$view.' does not exist.');
    }
}