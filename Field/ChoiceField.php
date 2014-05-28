<?php
namespace NeuroSYS\DoctrineDatatables\Field;

use Doctrine\ORM\QueryBuilder;

class ChoiceField extends AbstractField
{
    public function filter(QueryBuilder $qb)
    {
        $values = @explode(',', $this->getSearch());

        $orx = $qb->expr()->orX();
        foreach ($this->getSearchFields() as $i => $field) {
            $orx->add(
                $qb->expr()->in($field, $values)
            );
        }

        return $orx;
    }

}
