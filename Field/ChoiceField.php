<?php
namespace NeuroSYS\DoctrineDatatables\Field;


use Doctrine\ORM\QueryBuilder;

class ChoiceField extends Field
{
    public function filter(QueryBuilder $qb)
    {
        $values = @explode(',', $this->getSearch());

        return $qb->expr()->in($this->getFullName(), $values);
    }

} 