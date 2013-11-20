<?php
namespace NeuroSYS\DoctrineDatatables\Field;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Finder\Exception\OperationNotPermitedException;

class Entity extends AbstractField
{
    /**
     * @var string
     */
    protected $className;

    protected $relations = array();

    public function getRelation($name)
    {
        if (!isset($this->relations[$name])) {
            $this->relations[$name] = new self(null, $name);
        }
        return $this->relations[$name];
    }

    public function __construct($className, $name = null, $alias = null)
    {
        $this->className  = $className;

        if (!$name) { // root entity doesnt have to have na alias
            $name = self::generateAlias('x');
        }
        parent::__construct($name, $alias);
    }

    public function isJoined(QueryBuilder $qb)
    {
        /**
         * @var Join[] $join
         */
        $joins = $qb->getDQLPart('join');
        foreach ($joins as $join) {
            foreach ($join as $j) {
                if ($j->getAlias() == $this->getAlias()) { // already joined
                    return true;
                }
            }
        }
        return false;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function join(QueryBuilder $qb)
    {
        if (false === $this->isJoined($qb) && $this->getParent()) { // dont join root entity
            $qb->leftJoin($this->getFullName(), $this->getAlias());
        }

        return parent::join($qb);
    }
}