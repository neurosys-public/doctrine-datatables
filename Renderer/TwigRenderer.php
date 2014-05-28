<?php
namespace NeuroSYS\DoctrineDatatables\Renderer;

use NeuroSYS\DoctrineDatatables\RendererInterface;

class TwigRenderer implements RendererInterface
{
    private $environment;

    public function __construct(\Twig_Environment $env)
    {
        $this->environment = $env;
    }

    public function render($template, array $values = array())
    {
        return $this->environment->render($template, $values);
    }

}
