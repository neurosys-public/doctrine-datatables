<?php
namespace NeuroSYS\DoctrineDatatables\Field;

use Doctrine\ORM\QueryBuilder;

class NumberField extends RangeField
{
    public function filter(QueryBuilder $qb)
    {
        if (strpos($this->getSearch(), ',') === false) {
            $expr = $qb->expr()->orX();
            foreach ($this->getSearchFields() as $i => $field) {
                $var = preg_replace('/[^a-z0-9]*/i', '', $field) . '_' . $i;
                $expr->add(
                    $qb->expr()->eq($field, ':'.$var)
                );
                $qb->setParameter($var, (float) $this->getSearch());
            }
        } else {
            $expr = parent::filter($qb);
        }

        return $expr;
    }

}
