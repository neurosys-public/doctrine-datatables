<?php
namespace NeuroSYS\DoctrineDatatables\Field;

use Doctrine\ORM\QueryBuilder;

class BooleanField extends ChoiceField
{
    /**
     * @param QueryBuilder $qb
     * @param bool|false $global decide is filter or global search
     * @return \Doctrine\ORM\Query\Expr\Orx
     */
    public function filter(QueryBuilder $qb, $global = false)
    {
        $orx = $qb->expr()->orX();
        foreach ($this->getSearchFields() as $field) {
            $var = preg_replace('/[^a-z0-9]*/i', '', $field) . '_' . $this->getIndex() . ($global ? 'g' : '');

            $qb->setParameter($var, $this->getSearch($global));

            $orx->add($qb->expr()->eq($field, ':'.$var));
        }

        return $orx;
    }
}
