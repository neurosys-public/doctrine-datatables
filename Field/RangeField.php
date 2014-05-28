<?php
namespace NeuroSYS\DoctrineDatatables\Field;

use Doctrine\ORM\QueryBuilder;

abstract class RangeField extends TextField
{
    public function filter(QueryBuilder $qb)
    {
        @list($from, $to) = @explode(',', $this->getSearch());

        $expr = $qb->expr()->andX();
        list ($searchField,) = $this->getSearchFields();

        if ($from) {
            $expr->add(
                $qb->expr()->gte($searchField, ':from_'.$this->getIndex())
            );
            $qb->setParameter('from_'.$this->getIndex(), $from);
        }
        if ($to) {
            $expr->add(
                $qb->expr()->lte($searchField, ':to_'.$this->getIndex())
            );
            $qb->setParameter('to_'.$this->getIndex(), $to);
        }

        return $expr;
    }

}
