<?php
/**
 * Created by PhpStorm.
 * User: b.pasinski
 * Date: 26.11.13
 * Time: 11:16
 */

namespace NeuroSYS\DoctrineDatatables\Field;


use Doctrine\ORM\QueryBuilder;

class EmptyField extends AbstractField
{
    public function select(QueryBuilder $qb)
    {

    }

    public function getName()
    {
        return null;
    }

    public function getAlias()
    {
        return null;
    }

    public function order(QueryBuilder $qb, $dir = 'asc')
    {

    }
} 