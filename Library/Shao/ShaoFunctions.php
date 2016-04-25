<?php

namespace Library\Shao;

use Library\Container\Container;
use Library\Http\View;
use Symfony\Component\Yaml\Exception\RuntimeException;

class ShaoFunctions
{
    private $shao;
    private $container;
    private $router;

    public function __construct(Container $container, Shao $shao)
    {
        $this->shao = $shao;
        $this->container = $container;
        $this->router = $container->resolveInstance('router');
    }

    public function layout($str)
    {
        $requestedFile = __DIR__.'/../../'.View::VIEW_FOLDER.'/'.str_replace('.', '/', $str);

        $validExtensions = ['.php', '.html'];
        $validShaoExtensions = ['.shao.php', '.shao.html'];

        foreach ($validExtensions as $validExtension)
        {
            if (file_exists($requestedFile.$validExtension))
            {
                return '<?php $viewFactory->setLayoutFile(\''.$requestedFile.$validExtension.'\'); ?>';
            }
        }

        foreach ($validShaoExtensions as $validShaoExtension)
        {
            if (file_exists($requestedFile.$validShaoExtension))
            {
                return '<?php $viewFactory->setLayoutFile(\''.$this->shao->parseFile($requestedFile.$validShaoExtension).'\'); ?>';
            }
        }

        throw new RuntimeException('File '.$requestedFile.' does not exist.');
    }

    public function section($str)
    {
        return '<?php $viewFactory->startSection(\''.$str.'\'); ?>';
    }

    public function stop()
    {
        return '<?php $viewFactory->endSection(); ?>';
    }

    public function _yield($str)
    {
        return '<?php echo $viewFactory->getSection(\''.$str.'\'); ?>';
    }

    public function _include($str)
    {
        $requestedFile = __DIR__.'/../../'.View::VIEW_FOLDER.'/'.str_replace('.', '/', $str);

        $validExtensions = ['.php', '.html'];
        $validShaoExtensions = ['.shao.php', '.shao.html'];

        foreach ($validExtensions as $validExtension)
        {
            if (file_exists($requestedFile.$validExtension))
            {
                return file_get_contents($requestedFile.$validExtension);
            }
        }

        foreach ($validShaoExtensions as $validShaoExtension)
        {
            if (file_exists($requestedFile.$validShaoExtension))
            {
                return file_get_contents($this->shao->parseFile($requestedFile.$validShaoExtension));
            }
        }

        throw new RuntimeException('File '.$requestedFile.' does not exist.');
    }

    public function inject($class, $varName = null)
    {
        if (is_null($varName))
        {
            $varName = lcfirst(substr($class, strrpos($class, '\\') + 1));
        }

        return '<?php $'.$varName.' = $viewFactory->inject(\''.$class.'\'); ?>';
    }

    public function path($action, $var = null)
    {
        if (is_null($var))
        {
            return $this->router->getUri($action);
        }

        if (!is_array($var))
        {
            return $this->router->getUri($action, [$var]);
        }

        return $this->router->getUri($action, $var);
    }
}
