<?php

namespace NeuroSYS\DoctrineDatatables\Field;

use Doctrine\ORM\QueryBuilder;

abstract class Field
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
     * @var Field
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
     * Is this field sortable and sorted
     *
     * @var bool
     */
    protected $sort = false;

    /**
     * Sort order for this column
     *
     * @var string
     */
    protected $sortDir = 'asc';


    /**
     * Field path
     *
     * @var array
     */
    protected $path;

    /**
     * Alias index used to generate alias for a field
     * @var int
     */
    private static $aliasIndex = 1;

    public function __construct($name, $alias = null)
    {
        $this->name    = $name;
        $this->alias   = $alias ? $alias : self::generateAlias($name);
    }

    public static function generateAlias($name)
    {
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
        $this->alias = $alias;

        return $this;
    }

    public function setParent(Field $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    public function setSearch($search)
    {
        $this->search = $search;

        return $this;
    }

    public function getSearch()
    {
        return $this->search;
    }

    public function setSort($sort)
    {
        $this->sort = (bool)$sort;

        return $this;
    }

    public function isSort()
    {
        return $this->sort;
    }

    public function setSortDir($dir)
    {
        $this->sortDir = $dir;

        return $this;
    }

    public function getSortDir()
    {
        return $this->sortDir;
    }

    public function getParent()
    {
        return $this->parent;
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
        return $this->searchable;
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
     * Gets full name containing entity alias and field name
     *
     * @return string
     */
    public function getFullName()
    {
        return ($this->getParent() ? $this->getParent()->getAlias() . '.' : '') . $this->name;
    }

    public function isSearch()
    {
        return $this->isSearchable()
            && $this->getSearch() != '';
    }

    /**
     * @param QueryBuilder $qb
     * @return self
     */
    public function filter(QueryBuilder $qb)
    {
        $qb->setParameter($this->getName(), '%'.$this->getSearch().'%');

        return $qb->expr()->like($this->getFullName(), ':' . $this->getName());
    }

    /**
     * @return array Field path
     */
    public function getPath()
    {
        return $this->path ?: array($this->getName());
    }

    /**
     * @param $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @param QueryBuilder $qb
     * @return self
     */
    public function select(QueryBuilder $qb)
    {
        $qb->addSelect($this->getFullName() . ' as ' . $this->getAlias());
    }

    /**
     * @param QueryBuilder $qb
     * @return $this
     */
    public function order(QueryBuilder $qb)
    {
        $qb->addOrderBy($this->getFullName(), $this->getSortDir());
    }

    public function join(QueryBuilder $qb)
    {
        if ($this->getParent()) {
            $this->getParent()->join($qb);
        }
        return $this;
    }

    public function format(array $values)
    {
        return $values[$this->getAlias()];
    }
}