<?php

namespace Library\Configuration;

use DOMDocument;

class Config
{
    protected $vars;

    public function __construct()
    {
        $this->vars = array();
    }

    public function get($var, $default = null)
    {
        if (sizeof($this->vars) == 0)
        {
            $xml = new DOMDocument;
            $xml->load(__DIR__.'/../../Config/app.xml');
            
            $elements = $xml->getElementsByTagName('define');
            
            foreach ($elements as $element)
                $this->vars[$element->getAttribute('var')] = $element->getAttribute('value');
        }
        
        if (!isset($this->vars[$var]))
        {
            return $default;
        }

        switch (strtolower($this->vars[$var]))
        {
            case 'true':
                return true;
            case 'false':
                return false;
        }

        return $this->vars[$var];
    }
}
