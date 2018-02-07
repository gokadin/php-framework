<?php

namespace Library\Console\Modules\Generator;

class Templates
{
    const tab = '    ';

    public function generateRequest(string $path, bool $authenticated): string
    {
        $str = '<?php'.PHP_EOL.PHP_EOL;
        $str .= 'namespace App\\Http\\Requests';
        $namespaceSegments = explode('/', $path);
        for ($i = 0; $i < sizeof($namespaceSegments) - 1; $i++)
        {
            $str .= '\\'.$namespaceSegments[$i];
        }
        $str .= ';'.PHP_EOL.PHP_EOL;

        if (sizeof($namespaceSegments) > 1)
        {
            $authenticated
                ? $str .= 'use App\\Http\\Requests\\AuthenticatedReqest;'.PHP_EOL.PHP_EOL
                : $str .= 'use App\\Http\\Requests\\Request;'.PHP_EOL.PHP_EOL;
        }

        $str .= 'class '.$namespaceSegments[sizeof($namespaceSegments) - 1].' extends ';

        $authenticated
            ? $str .= 'AuthenticatedRequest'.PHP_EOL
            : $str .= 'Request'.PHP_EOL;

        $str .= '{'.PHP_EOL;

        $str .= self::tab.'public function authorize(): bool'.PHP_EOL;
        $str .= self::tab.'{'.PHP_EOL;
        $str .= self::tab.self::tab.'return true;'.PHP_EOL;
        $str .= self::tab.'}'.PHP_EOL.PHP_EOL;

        $str .= self::tab.'public function rules(): array'.PHP_EOL;
        $str .= self::tab.'{'.PHP_EOL;
        $str .= self::tab.self::tab.'return [];'.PHP_EOL;
        $str .= self::tab.'}'.PHP_EOL;

        $str .= '}'.PHP_EOL;

        return $str;
    }

    public function generateTranslator(string $path, bool $authenticated): string
    {
        $content = '<?php'.PHP_EOL.PHP_EOL;
        $content .= 'namespace App\\Http\\Translators';
        $namespaceSegments = explode('/', $path);
        for ($i = 0; $i < sizeof($namespaceSegments) - 1; $i++)
        {
            $content .= '\\'.$namespaceSegments[$i];
        }
        $content .= ';'.PHP_EOL.PHP_EOL;

        $content .= 'use Library\Http\Request;'.PHP_EOL;

        if (sizeof($namespaceSegments) > 1)
        {
            $authenticated
                ? $content .= 'use App\\Http\\Translators\\AuthenticatedTranslator;'.PHP_EOL.PHP_EOL
                : $content .= 'use App\\Http\\Translators\\Translator;'.PHP_EOL.PHP_EOL;
        }
        else
        {
            $content .= PHP_EOL;
        }

        $content .= 'class '.$namespaceSegments[sizeof($namespaceSegments) - 1].' extends ';

        $authenticated
            ? $content .= 'AuthenticatedTranslator'.PHP_EOL
            : $content .= 'Translator'.PHP_EOL;

        $content .= '{'.PHP_EOL;

        $content .= self::tab.'public function translateRequest(Request $request): array'.PHP_EOL;
        $content .= self::tab.'{'.PHP_EOL.PHP_EOL;
        $content .= self::tab.'}'.PHP_EOL.PHP_EOL;

        $content .= self::tab.'public function translateResponse(): array'.PHP_EOL;
        $content .= self::tab.'{'.PHP_EOL.PHP_EOL;
        $content .= self::tab.'}'.PHP_EOL;

        $content .= '}'.PHP_EOL;

        return $content;
    }

    public function generateController(string $path, bool $api): string
    {
        $content = '<?php'.PHP_EOL.PHP_EOL;
        $content .= 'namespace App\\Http\\Controllers';
        $namespaceSegments = explode('/', $path);
        for ($i = 0; $i < sizeof($namespaceSegments) - 1; $i++)
        {
            $content .= '\\'.$namespaceSegments[$i];
        }
        $content .= ';'.PHP_EOL.PHP_EOL;

        if (sizeof($namespaceSegments) > 1)
        {
            $api
                ? $content .= 'use App\\Http\\Controllers\\ApiController;'.PHP_EOL.PHP_EOL
                : $content .= 'use App\\Http\\Controllers\\Controller;'.PHP_EOL.PHP_EOL;
        }

        $content .= 'class '.$namespaceSegments[sizeof($namespaceSegments) - 1].' extends ';

        $api
            ? $content .= 'ApiController'.PHP_EOL
            : $content .= 'Controller'.PHP_EOL;

        $content .= '{'.PHP_EOL;
        $content .= '}'.PHP_EOL;

        return $content;
    }
}