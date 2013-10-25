<?php
namespace NeuroSYS\DoctrineDatatables\Field;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Finder\Exception\OperationNotPermitedException;

class Entity extends Field
{
    /**
     * @var string
     */
    protected $className;

    protected $joined = false;

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

    public function setJoined($joined)
    {
        $this->joined = $joined;

        return $this;
    }

    public function isJoined()
    {
        return $this->joined;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function join(QueryBuilder $qb)
    {
        if (false === $this->isJoined() && $this->getParent()) { // dont join root entity
            $qb->leftJoin($this->getFullName(), $this->getAlias());

            $this->setJoined(true);
        }

        return parent::join($qb);
    }
}