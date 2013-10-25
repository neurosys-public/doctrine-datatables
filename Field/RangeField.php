<?php
namespace NeuroSYS\DoctrineDatatables\Field;

use Doctrine\ORM\QueryBuilder;

abstract class RangeField extends TextField
{
    public function filter(QueryBuilder $qb)
    {
        @list($from, $to) = @explode(',', $this->getSearch());

        $expr = $qb->expr()->andX();

        if ($from) {
            $expr->add(
                $qb->expr()->gte($this->getFullName(), ':from_'.$this->getName())
            );
            $qb->setParameter('from_'.$this->getName(), $from);
        }
        if ($to) {
            $expr->add(
                $qb->expr()->lte($this->getFullName(), ':to_'.$this->getName())
            );
            $qb->setParameter('to_'.$this->getName(), $to);
        }
        return $expr;
    }

} 