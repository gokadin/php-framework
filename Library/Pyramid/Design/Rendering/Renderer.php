<?php

namespace Library\Pyramid\Design\Rendering;

class Renderer
{
    /**
     * @var int
     */
    private $indent;

    public function render(Node $node, int $indent = 0): string
    {
        $this->indent = $indent;

        return $this->renderNodes($node);
    }

    public function renderScss(Node $node, int $indent = 0): string
    {
        $this->indent = $indent;

        return $this->renderCss($node);
    }

    private function renderCss(Node $node): string
    {
        $css = tab($this->indent).'.'.$node->classes()[0].' {'.PHP_EOL;

        $this->indent++;

        foreach ($node->cssRules() as $modifier => $rule)
        {
            foreach ($rule as $key => $value)
            {
                $css .= tab($this->indent).$key.': '.$value.';'.PHP_EOL;
            }
        }

        foreach ($node->children() as $child)
        {
            $css .= $this->renderCss($child);
        }

        $this->indent--;

        $css .= tab($this->indent).'}'.PHP_EOL;

        return $css;
    }

    private function renderNodes(Node $node): string
    {
        $html = tab($this->indent).$node->render().PHP_EOL;
        if ($node->text() != '')
        {
            $html .= tab($this->indent + 1).$node->text().PHP_EOL;
        }

        $this->indent++;

        foreach ($node->children() as $child)
        {
            $html .= $this->renderNodes($child);
        }

        $this->indent--;

        if ($node->shouldClose())
        {
            $html .= tab($this->indent) . $node->close().PHP_EOL;
        }

        return $html;
    }
}