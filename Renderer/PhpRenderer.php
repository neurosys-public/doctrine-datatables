<?php
namespace NeuroSYS\DoctrineDatatables\Renderer;

use NeuroSYS\DoctrineDatatables\RendererInterface;

class PhpRenderer implements RendererInterface
{
    public function render($template, array $values = array())
    {
        ob_start();
        extract($values);
        include $template;

        return ob_get_clean();
    }
}
