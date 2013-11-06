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

    /**
     * @param Field[] $fields
     * @return $this
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @param Field $field
     * @return $this
     */
    public function addField(Field $field)
    {
        $this->fields[] = $field;

        return $this;
    }

    /**
     * @return Field[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /*
     * @param QueryBuilder $qb
     *
     * @return \Doctrine\ORM\Query\Expr\Base
     */
    public function filter(QueryBuilder $qb)
    {
        // explode spaces so each field can be found separately
        $searches = explode(' ', $this->getSearch());
        $orx = $qb->expr()->orX();
        foreach ($this->fields as $field) {
            foreach ($searches as $i => $search) {
                $qb->setParameter($field->getName().'_'.$i, ($i > 0 ? '%' : '') . trim($search) . '%');
                $orx->add($qb->expr()->like($field->getFullName(), ':'.$field->getName().'_'.$i));
            }
        }
        return $orx;
    }

    public function select(QueryBuilder $qb)
    {
        foreach ($this->fields as $field) {
            $field->select($qb);
        }
    }

    public function format(array $values)
    {
        if ($this->formatter) {
            return call_user_func_array($this->formatter, array($this, $values));
        }
        $items = array();
        foreach ($this->fields as $field) {
            $items[] = $field->getValue($values);
        }
        return implode(' ', $items);
    }

    public function order(QueryBuilder $qb, $dir = 'asc')
    {
        foreach ($this->fields as $field) {
            $qb->addOrderBy($field->getFullName(), $dir);
        }
    }

    /**
     * @param QueryBuilder $qb
     * @return MultiField
     */
    public function join(QueryBuilder $qb)
    {
        foreach ($this->fields as $field) {
            $field->join($qb);
        }
        return $this;
    }

    public function &getValue(&$values)
    {
        foreach ($this->getFields() as $subField) {
            $value = &$subField->getValue($values);
            $value = $subField->format($values);
        }
        $values[$this->getName()] = null;
        return $values[$this->getName()];
    }

} 