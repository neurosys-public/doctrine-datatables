<?php
namespace NeuroSYS\DoctrineDatatables\Field;

class NumberField extends RangeField
{
    public function format($values, $value = null)
    {
        if (isset($this->options['precision'])) {
            $value = round($value, $this->options['precision']);
        }
        return (float) $value;
    }
}