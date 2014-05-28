<?php
namespace NeuroSYS\DoctrineDatatables;

interface RendererInterface
{
    public function render($template, array $values = array());
}
