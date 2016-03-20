<?php
namespace NeuroSYS\DoctrineDatatables\Field;

use Doctrine\ORM\QueryBuilder;

abstract class RangeField extends TextField
{
    /**
     * @param QueryBuilder $qb
     * @param bool|false $global decide is filter or global search
     * @return \Doctrine\ORM\Query\Expr\Andx
     */
    public function filter(QueryBuilder $qb, $global = false)
    {
        @list($from, $to) = @explode(',', $this->getSearch());

        $expr = $qb->expr()->andX();
        list ($searchField,) = $this->getSearchFields();

        if ($from) {
            $var = 'from_'.$this->getIndex() . ($global ? 'g' : '');
            $expr->add(
                $qb->expr()->gte($searchField, ':' . $var)
            );
            $qb->setParameter($var, $from);
        }
        if ($to) {
            $var = 'to_'.$this->getIndex() . ($global ? 'g' : '');
            $expr->add(
                $qb->expr()->lte($searchField, ':' . $var)
            );
            $qb->setParameter($var, $to);
        }

        return $expr;
    }

}
