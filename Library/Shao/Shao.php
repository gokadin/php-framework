<?php

namespace Library\Shao;

use Library\Container\Container;
use Library\Http\View;

class Shao
{
    const SHAO_FOLDER = 'Storage/Framework/Shao/';

    protected $shaoLogic;
    protected $shaoFunctions;
    protected $currentView;

    public function __construct(Container $container)
    {
        $this->shaoLogic = new ShaoLogic();
        $this->shaoFunctions = new ShaoFunctions($container, $this);
    }

    public function parseFile($file)
    {
        $content = file_get_contents($file);
        $cachedFileName = $this->generateCachedFileName($file, $content);

        if (env('APP_ENV') == 'production' && !$this->isFileChanged($cachedFileName))
        {
            return $cachedFileName;
        }

        $this->parseRawPhp($content);
        $this->parseRawPhpAndEscape($content);
        $this->parseEchoAndEscape($content);
        $this->parseEcho($content);
        $this->parseAngularSymbol($content);
        $this->parseLogic($content);
        $this->parseFunctions($content);

        $this->deleteOldFiles($cachedFileName);
        file_put_contents($cachedFileName, $content);

        return $cachedFileName;
    }

    private function isFileChanged($fileName)
    {
        return !file_exists($fileName);
    }

    private function generateCachedFileName($file, $content)
    {
        $fileName = explode('.', explode(View::VIEW_FOLDER.'/', $file)[1])[0];

        $this->currentView = $fileName;

        $fileName = str_replace('/', '-', $fileName);
        $fileName .= '-';

        return __DIR__.'/../../'.self::SHAO_FOLDER.$fileName.md5($content);
    }

    protected function deleteOldFiles($fileName)
    {
        $fileName = substr($fileName, 0, strrpos($fileName, '-'));

        $files = glob($fileName.'*');

        array_map('unlink', $files);
    }

    /* PARSING FUNCTIONS */

    private function parseEchoAndEscape(&$str)
    {
        $str = str_replace('{!!', '<?php echo ', $str);
        $str = str_replace('!!}', '; ?>', $str);
    }

    private function parseRawPhp(&$str)
    {
        $str = str_replace('{{{', '<?php htmlEntities(', $str);
        $str = str_replace('}}}', ') ?>', $str);
    }

    private function parseRawPhpAndEscape(&$str)
    {
        $str = str_replace('{{!!', '<?php ', $str);
        $str = str_replace('!!}}', ' ?>', $str);
    }

    private function parseEcho(&$str)
    {
        $str = preg_replace('/(?<!\@)\{\{([^\}]*)\}\}/', '<?php echo htmlentities(${1}); ?>', $str);
    }

    private function parseAngularSymbol(&$str)
    {
        $str = str_replace('@{{', '{{', $str);
    }

    private function parseLogic(&$str)
    {
        $logicString = '';
        $bracketCounter = 0;
        $found = false;
        $nameIsBuilt = false;
        $canStopLooking = false;
        $foundStrings = array();
        for ($i = 0; $i < strlen($str); $i++)
        {
            if ($str[$i] == '@' && !$found)
            {
                if ($i < strlen($str) - 1 && !ctype_alpha($str[$i + 1])) continue;

                $found = true;
                $nameIsBuilt = false;
                $canStopLooking = false;
                $logicString = $str[$i];
                continue;
            }

            if (!$found) continue;

            if (!$nameIsBuilt && ctype_space($str[$i]))
                $nameIsBuilt = true;

            if (!$canStopLooking && $i == strlen($str) - 1)
            {
                $logicString .= $str[$i];
                $foundStrings[] = $logicString;
                continue;
            }

            if (!$canStopLooking && $nameIsBuilt && $str[$i] != ' ' && $str[$i] != '(')
            {
                $foundStrings[] = $logicString;
                $found = false;
                $canStopLooking = true;
                $bracketCounter = 0;
                $nameIsBuilt = false;
                continue;
            }

            if ($str[$i] == '(')
            {
                $bracketCounter++;
                $canStopLooking = true;
            }
            else if ($str[$i] == ')')
                $bracketCounter--;

            $logicString .= $str[$i];

            if ($canStopLooking && $bracketCounter == 0)
            {
                $foundStrings[] = $logicString;
                $found = false;
            }
        }

        foreach ($foundStrings as $foundString)
        {
            if (!strpos($foundString, '('))
            {
                $logicName = trim(substr($foundString, 1));
                $logicBody = null;
            }
            else
            {
                $logicName = trim(substr(strstr($foundString, '(', true), 1));
                $logicBody = substr(substr(strstr($foundString, '('), 1), 0, -1);
            }

            $functionName = $this->shaoLogic->convertToFunctionName($logicName);
            if (!$functionName) continue;

            if ($logicBody == null)
                $result = $this->shaoLogic->$functionName();
            else
                $result = $this->shaoLogic->$functionName($logicBody);

            $str = str_replace($foundString, $result, $str);
        }
    }

    private function parseFunctions(&$str)
    {
        $functionRegex = '/@[a-zA-Z0-9]+\((\'.+\')?\)/';

        $str = preg_replace_callback($functionRegex, [$this, 'parseFunctionReplaceCallback'], $str);
    }

    private function parseFunctionReplaceCallback($string)
    {
        $string = $string[0];
        $string = trim($string);
        $functionName = trim(strstr(str_replace('@', '', $string), '(', true));
        $functionArgs = substr(substr(strstr($string, '('), 0, -1), 1);
        $functionArgs = explode(',', $functionArgs);
        foreach ($functionArgs as &$functionArg) {
            $functionArg = trim($functionArg);

            if (substr($functionArg, 0, 1) == '\'' && substr($functionArg, -1) == '\'')
                $functionArg = $this->removeSingleQuotes($functionArg);
        }

        // exception
        if ($functionName == 'yield')
        {
            return $this->shaoFunctions->_yield($functionArgs[0], $this->currentView);
        }

        if ($functionName == 'include')
        {
            return $this->shaoFunctions->_include($functionArgs[0]);
        }

        if (!method_exists($this->shaoFunctions, $functionName))
        {
            return $string;
        }

        return call_user_func_array([$this->shaoFunctions, $functionName], $functionArgs);
    }

    private function removeSingleQuotes($string)
    {
        return substr(substr($string, 0, -1), 1);
    }
}
