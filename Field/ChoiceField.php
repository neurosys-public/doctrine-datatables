<?php
namespace NeuroSYS\DoctrineDatatables\Field;

use Doctrine\ORM\QueryBuilder;

class ChoiceField extends AbstractField
{
    /**
     * @param QueryBuilder $qb
     * @param bool|false $global decide is filter or global search
     * @return \Doctrine\ORM\Query\Expr\Orx
     */
    public function filter(QueryBuilder $qb, $global = false)
    {
        $values = @explode(',', $this->getSearch($global));

        $orx = $qb->expr()->orX();
        foreach ($this->getSearchFields() as $i => $field) {
            $orx->add(
                $qb->expr()->in($field, $values)
            );
        }

        return $orx;
    }

}
