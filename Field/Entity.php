<?php
namespace NeuroSYS\DoctrineDatatables\Field;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use NeuroSYS\DoctrineDatatables\Table;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class Entity extends AbstractField
{

    /**
     * @var string Field name
     */
    protected $name;

    /**
     * @var string Field alias
     */
    protected $alias;

    /**
     * Alias index used to generate alias for a field
     * @var int
     */
    private static $aliasIndex = 1;

    /**
     * @var AbstractField[]
     */
    protected $fields = array();

    /**
     * @var Entity[]
     */
    protected $relations = array();

    /**
     * @var string Join type
     */
    protected $joinType;

    public static function generateAlias($name)
    {
        if (!$name) {
            $name = 'x';
        }
        $name = preg_replace('/[^A-Z]/i', '', $name);

        return $name[0] . (self::$aliasIndex++);
    }
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function setAlias($alias)
    {
        if (!$alias) {
            $alias = $this->generateAlias($this->getName() ?: 'x');
        }
        $this->alias = $alias;

        return $this;
    }

    /**
     * @param string $joinType
     */
    public function setJoinType($joinType)
    {
        $this->joinType = $joinType;

        return $this;
    }

    /**
     * @return string $joinType
     */
    public function getJoinType()
    {
        return $this->joinType;
    }

    /**
     * @return array Field path
     */
    public function getPath()
    {
        $path = array();
        if ($this->getParent()) {
            $path = $this->getParent()->getPath();
        }
        $path[] = $this->getName();

        return $path;
    }

    public function __construct(Table $table, $name, $alias, array $options = array())
    {
        if (empty($name) || empty($alias)) {
            throw new \Exception("Name and alias must not be empty");
        }

        parent::__construct($table, $options);

        $this->setName($name);
        $this->setAlias($alias);

        $table->addEntity($this);
    }

    /**
     * Gets this field alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Gets this field name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param  AbstractField $field
     * @return $this
     */
    public function setField($name, AbstractField $field)
    {
        $this->fields[$name] = $field;

        return $this;
    }

    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Gets full name containing entity alias and field name
     *
     * @return string
     */
    public function getFullName()
    {
        return ($this->getParent() ? $this->getParent()->getAlias() . '.' : '') . $this->getName();
    }

    public function getField($index)
    {
        return $this->fields[$index];
    }

    /**
     * @param  QueryBuilder $qb
     * @return self
     */
    public function select(QueryBuilder $qb)
    {
        $qb->addSelect($this->getAlias());
    }

    public function join($name, $alias, $type = 'LEFT')
    {
        if (!isset($this->relations[$name])) {
            if ($child = $this->getTable()->getEntity($alias)) {
                $this->relations[$name] = $child;
                $child->setJoinType($type);

                return $child;
            } else {
                $child = new self($this->getTable(), $name, $alias);
                $child->setParent($this);
                $child->setJoinType($type);
                $this->relations[$name] =  $child;
            }
        }

        return $this->relations[$name];
    }

    public function getClassName()
    {
        if ($this->getParent()) {
            $class = $this->getParent()->getClassName();

            return $this->getTable()->getManager()->getClassMetadata($class)->getAssociationTargetClass($this->getName());
        }

        return $this->getTable()->getManager()->getClassMetadata($this->getName());
    }

    public function getPrimaryKeys()
    {
        //$metadata = $this->getTable()->getManager()->getClassMetadata($this->getClassName());
        //return $metadata->getIdentifierFieldNames();
        return array('id'); // FIXME
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

    public function format($values, $value = null)
    {
        $result = array();
        $accessor = new PropertyAccessor();

        foreach ($this->getFields() as $name => $field) {
            $accessPath = $field->getPath(0);
            if (is_array($value)) {
                $accessPath = '['.$accessPath.']';
            }

            return $result[$name] = $field->format($value, $accessor->getValue($value, $accessPath));
        }

        return $result;
    }

    public function getSelect()
    {
        $select = array();
        foreach ($this->getFields() as $field) {
            $select = array_merge_recursive($select, $field->getSelect());
        }

        return $select;
    }

    /**
     * @param  QueryBuilder $qb
     * @return Expr
     */
    public function filter(QueryBuilder $qb)
    {
        $orx = $qb->expr()->orX();
        foreach ($this->getFields() as $field) {
            if ($field->isSearch()) {
                $orx->add($field->filter($qb));
            }
        }
        if ($orx->count() > 0) {
            $qb->andWhere($orx);
        }

        return $orx;
    }
}
