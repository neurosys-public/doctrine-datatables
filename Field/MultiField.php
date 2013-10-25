<?php
namespace NeuroSYS\DoctrineDatatables\Field;

use Doctrine\ORM\QueryBuilder;

class MultiField extends Field
{
    /**
     * @var Field[]
     */
    protected $fields = array();

    public function __construct($name, $fields = array())
    {
        parent::__construct($name);

        $this->setFields($fields);
    }

    public function setFields(array $fields)
    {
        $this->fields = $fields;

        return $this;
    }

    public function addField(Field $field)
    {
        $this->fields[] = $field;

        return $this;
    }

    /*
     * @param QueryBuilder $qb
     *
     * @return \Doctrine\ORM\Query\Expr\Base
     */
    public function filter(QueryBuilder $qb)
    {
        $expr = $qb->expr()->orX();

        foreach ($this->fields as $field) {
            $expr->add($field->filter($qb));
        }
        return $expr;
    }

    public function select(QueryBuilder $qb)
    {
        foreach ($this->fields as $field) {
            $field->select($qb);
        }
    }

    public function format(array $values)
    {
        $items = array();
        foreach ($this->fields as $field) {
            $items[] = $values[$field->getAlias()];
        }
        return implode(' ', $items); // TODO: make separator configurable
    }

    public function order(QueryBuilder $qb)
    {
        foreach ($this->fields as $field) {
            $qb->addOrderBy($field->getFullName(), $field->getSortDir());
        }
    }
} 