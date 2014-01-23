<?php
/**
 * Created by PhpStorm.
 * User: b.pasinski
 * Date: 17.01.14
 * Time: 12:33
 */

namespace NeuroSYS\DoctrineDatatables;


interface RendererInterface
{
    public function render($template, array $values = array());
}