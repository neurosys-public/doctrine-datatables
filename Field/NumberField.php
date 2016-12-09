<?php
namespace NeuroSYS\DoctrineDatatables\Field;

use Doctrine\ORM\QueryBuilder;

class NumberField extends RangeField
{
    /**
     * @param QueryBuilder $qb
     * @param bool|false $global decide is filter or global search
     * @return \Doctrine\ORM\Query\Expr\Andx|\Doctrine\ORM\Query\Expr\Orx
     */
    public function filter(QueryBuilder $qb, $global = false)
    {
        if (strpos($this->getSearch($global), ',') === false) {
            $expr = $qb->expr()->orX();
            foreach ($this->getSearchFields() as $i => $field) {
                $var = preg_replace('/[^a-z0-9]*/i', '', $field) . '_' . $i . ($global ? 'g' : '');
                $expr->add(
                    $qb->expr()->eq($field, ':'.$var)
                );
                $qb->setParameter($var, (float) $this->getSearch($global));
            }
        } else {
            $expr = parent::filter($qb, $global);
        }

        return $expr;
    }

}
