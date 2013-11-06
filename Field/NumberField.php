<?php
namespace NeuroSYS\DoctrineDatatables\Field;

class NumberField extends RangeField
{
    public function format(array $values)
    {
        return (int) $this->getValue($values);
    }
}