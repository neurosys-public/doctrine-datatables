<?php
namespace NeuroSYS\DoctrineDatatables\Field;

class DateField extends RangeField
{
    /**
     * @var array
     */
    protected $options = array(
        'format' => 'Y-m-d',
    );

    /**
     * @param array $values
     * @return string
     */
    public function format(array $values)
    {
        $date = new \DateTime($values[$this->getAlias()]);
        return $date->format($this->options['format']);
    }
} 