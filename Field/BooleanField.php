<?php
namespace NeuroSYS\DoctrineDatatables\Field;


class BooleanField extends ChoiceField
{
    public function format(array $values)
    {
        return (bool)$values[$this->getAlias()];
    }
} 