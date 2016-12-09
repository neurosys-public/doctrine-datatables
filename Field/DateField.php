<?php
namespace NeuroSYS\DoctrineDatatables\Field;

class DateField extends RangeField
{
    /**
     * @param bool|false $global decide is filter or global search
     * @return string
     */
    public function getSearch($global = false)
    {
        @list($from, $to) = @explode(',', parent::getSearch($global));

        // prepare date range
        return
            ($from ? date('Y-m-d', strtotime($from)) . ' 00:00:00' : '')
            . ',' .
            ($to ? date('Y-m-d', strtotime($to)) . ' 23:59:59' : '')
            ;

    }
}
