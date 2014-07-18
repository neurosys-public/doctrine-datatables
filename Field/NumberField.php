<?php
namespace NeuroSYS\DoctrineDatatables\Field;

use Doctrine\ORM\QueryBuilder;

class NumberField extends RangeField
{
    public function filter(QueryBuilder $qb)
    {
        if (strpos($this->getSearch(),',') === false) {
            $search = $this->getSearch();
            $expr = $qb->expr()->andX();
            list ($searchField,) = $this->getSearchFields();
            if ($search) {
                $expr->add(
                    $qb->expr()->eq($searchField, ':from_'.$this->getIndex())
                );
                $qb->setParameter('from_'.$this->getIndex(), $search);
            }
        } else {
            $expr = parent::filter($qb);
        }

        return $expr;
    }

}
