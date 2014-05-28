<?php
namespace NeuroSYS\DoctrineDatatables\Field;

use Doctrine\ORM\QueryBuilder;

class BooleanField extends ChoiceField
{
    public function filter(QueryBuilder $qb)
    {
        $orx = $qb->expr()->orX();
        foreach ($this->getSearchFields() as $field) {
            $var = preg_replace('/[^a-z0-9]*/i', '', $field) . '_' . $this->getIndex();

            $qb->setParameter($var, $this->getSearch());

            $orx->add($qb->expr()->eq($field, ':'.$var));
        }

        return $orx;
    }
}
