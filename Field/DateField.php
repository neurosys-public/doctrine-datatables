<?php
namespace NeuroSYS\DoctrineDatatables\Field;

class DateField extends RangeField
{
    public function getSearch()
    {
        @list($from, $to) = @explode(',', parent::getSearch());

        // prepare date range
        return
            ($from ? date('Y-m-d', strtotime($from)) . ' 00:00:00' : '')
            . ',' .
            ($to ? date('Y-m-d', strtotime($to)) . ' 23:59:59' : '')
            ;

    }
}
