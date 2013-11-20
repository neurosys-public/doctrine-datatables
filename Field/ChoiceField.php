<?php
namespace NeuroSYS\DoctrineDatatables\Field;


use Doctrine\ORM\QueryBuilder;

class ChoiceField extends AbstractField
{
    public function filter(QueryBuilder $qb)
    {
        $values = @explode(',', $this->getSearch());

        return $qb->expr()->in($this->getSearchField(), $values);
    }

} 