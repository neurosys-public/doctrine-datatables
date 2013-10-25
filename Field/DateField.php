<?php
namespace NeuroSYS\DoctrineDatatables\Field;

class DateField extends RangeField
{
    public function format(array $values)
    {
        $date = new \DateTime($values[$this->getAlias()]);
        return $date->format('Y-m-d'); // TODO: make it configurable
    }
} 