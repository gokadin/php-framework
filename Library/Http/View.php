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

    public function processView(ViewFactory $viewFactory, Shao $shao)
    {
        $view = $this->basePath.'/'.str_replace('.', '/', $this->viewAction);

        $contentFile = $this->getContentFile($view, $shao);

        if (!is_null($this->vars))
        {
            extract($this->vars);
        }

        ob_start();
        require $contentFile;
        $content = ob_get_clean();

        if ($viewFactory->hasLayout())
        {
            ob_start();
            require $viewFactory->getLayoutFile();
            return ob_get_clean();
        }

        return $content;
    }

    protected function getContentFile($view, Shao $shao)
    {
        $validExtensions = ['.php', '.html'];
        $validShaoExtensions = ['.shao.php', '.shao.html'];

        foreach ($validExtensions as $validExtension)
        {
            if (file_exists($view.$validExtension))
            {
                return $view.$validExtension;
            }
        }

        foreach ($validShaoExtensions as $validShaoExtension)
        {
            if (file_exists($view.$validShaoExtension))
            {
                return $shao->parseFile($view.$validShaoExtension);
            }
        }

        throw new RuntimeException('File '.$view.' does not exist.');
    }
}