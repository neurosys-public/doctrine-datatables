<?php

namespace NeuroSYS\DoctrineDatatables\Field;

use Doctrine\ORM\QueryBuilder;
use NeuroSYS\DoctrineDatatables\Table;

abstract class AbstractField
{
    protected $searchFields = array();

    protected $selectFields = array();

    /**
     * @var Entity
     */
    protected $parent;

    /**
     * @var bool
     */
    protected $searchable = true;

    /**
     * Search string for this column
     *
     * @var string
     */
    protected $search;

    /**
     * Field path
     *
     * @var array
     */
    protected $path;

    /**
     * @var integer Field index in a Datatables request
     */
    protected $index;

    /**
     * Field template
     *
     * @var string
     */
    protected $template;

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @var Table
     */
    protected $table;

    protected $context;

    public function __construct(Table $table, $options = array())
    {
        $this->options = $options;
        $this->table   = $table;
    }

    public function setSelect(array $select)
    {
        $this->selectFields = $select;
    }

    public function setSearchFields(array $searchFields)
    {
        $this->searchFields = $searchFields;
    }

    /**
     * @param Entity $entity
     */
    public function setParent(Entity $entity)
    {
        $this->parent = $entity;

        return $this;
    }

    /**
     * @return Entity
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function setSearch($search)
    {
        $this->search = $search;

        return $this;
    }

    public function getSearch()
    {
        return isset($this->search) ? $this->search : $this->getTable()->getRequest()->get('sSearch', $this->getIndex());
    }

    /**
     * @param bool $searchable
     */
    public function setSearchable($searchable)
    {
        $this->searchable = $searchable;
    }

    /**
     * Whether this field is searchable
     *
     * @return bool
     */
    public function isSearchable()
    {
        return isset($this->searchable) ? $this->searchable : (bool) $this->getTable()->getRequest()->get('bSearchable', $this->getIndex());
    }

    public function isSearch()
    {
        return $this->isSearchable()
            && $this->getSearch() != '';
    }

    /**
     * @param  QueryBuilder $qb
     * @return self
     */
    public function filter(QueryBuilder $qb)
    {
        $orx = $qb->expr()->orX();
        foreach ($this->getSearchFields() as $i => $field) {
            $var = preg_replace('/[^a-z0-9]*/i', '', $field) . '_' . $i;
            $qb->setParameter($var, '%'.$this->getSearch().'%');
            $orx->add(
                $qb->expr()->like($field, ':' . $var)
            );
        }

        return $orx;
    }

    public function getSearchFields()
    {
        return $this->searchFields;
    }

    public function setContext($context)
    {
        $this->context = $context;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function getSelectPaths()
    {
        if (isset($this->options['context'])) {
            return array(explode('.', $this->options['context']));
        }
        $paths = array();
        foreach ($this->getSelect() as $entityAlias => $select) {
            if (!is_array($select)) {
                $select = array($select);
            }
            $entity = $this->getTable()->getEntity($entityAlias);
            if (!$entity) {
                throw new \Exception("Internal error, entity not found for alias " . $entityAlias);
            }
            foreach ($select as $fieldName) {
                $path = $entity->getPath();
                $path[] = $fieldName;
                $paths[] = $path;
            }
        }

        return $paths;
    }

    /**
     * @param $path
     * @return $this
     */
    /*public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }*/

    /**
     * Get(Entity alias => field) name pairs
     * @return array
     */
    public function getSelect()
    {
        return $this->selectFields;
    }

    public function isSortable()
    {
        return (bool) $this->getTable()->getRequest()->get('bSortable', $this->getIndex());
    }

    /**
     * @param  QueryBuilder $qb
     * @return $this
     */
    public function order(QueryBuilder $qb)
    {
        for ($i = 0; $i < $this->getTable()->getRequest()->get('iSortingCols'); $i++) {
            if ($this->getTable()->getRequest()->get('iSortCol', $i) == $this->getIndex()) {
                $dir = $this->getTable()->getRequest()->get('sSortDir', $i);
                foreach ($this->getSelect() as $entityAlias => $fields) {
                    foreach ($fields as $field) {
                        if (strpos(strtolower($field), ' as ') !== false) {
                            list(, $field) = preg_split('/ as /i', $field);
                        }
                        $qb->addOrderBy(($entityAlias ? $entityAlias . '.' : '') . $field, $dir == 'asc' ? 'asc' : 'desc');
                    }
                }
            }
        }

        return $this;
    }

    public function format($values, $value = null)
    {
        if ($this->template) {
            return $this->getTable()->getRenderer()->render($this->template, array('values' => $values, 'value' => $value, 'field' => $this));
        }

        return $value;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @param int $index
     */
    public function setIndex($index)
    {
        $this->index = $index;
    }

    /**
     * @return int
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @return Table
     */
    public function getTable()
    {
        return $this->table;
    }

    public function __toString()
    {
        return $this->getParent() . " => \n\t" . $this->getFullName() . ' as ' . $this->getAlias();
    }
}
