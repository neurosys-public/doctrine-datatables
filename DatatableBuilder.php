<?php
namespace NeuroSYS\DoctrineDatatables;

use Doctrine\ORM\EntityManager;
use NeuroSYS\DoctrineDatatables\Field\Entity;
use NeuroSYS\DoctrineDatatables\Field\MultiField;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;

class DatatableBuilder
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var Entity
     */
    private $entity;

    /**
     * @var array
     */
    private $fields = array();

    /**
     * @var MultiField
     */
    private $parent;

    /**
     * @var FieldRegistry
     */
    private $registry;

    public function __construct(EntityManager $em, array $request, FieldRegistry $registry = null)
    {
        $this->em       = $em;
        if (null === $registry) {
            $registry = new FieldRegistry();
        }
        $this->registry = $registry;
        $this->request  = $request;
    }

    /**
     * @param string $className Root entity class name
     * @param string $alias     Alias of root entity
     * @return $this
     */
    public function from($className, $alias = null)
    {
        $this->entity = new Entity($className, $alias);

        return $this;
    }

    /**
     * Get request parameter with datatables.js index style
     *
     * @param $name
     * @param $index
     * @param bool $default
     * @return mixed
     */
    public function getParameter($name, $index = null, $default = false)
    {
        $name .= (null !== $index ? '_' . $index : '');
        if (isset($this->request[$name])) {
            return $this->request[$name];
        }
        return $default;
    }

    public function multi($name)
    {
        $this->parent = new MultiField($name);

        // TODO: make it DRY @see self::add
        $index = count($this->fields);
        if ($this->getParameter('iSortCol', 0, -1) == $index) {
            $this->parent->setSort(true);
            $this->parent->setSortDir($this->getParameter('sSortDir', $index));
        }

        return $this;
    }

    public function end()
    {
        $this->fields[] = $this->parent;

        $this->parent = null;

        return $this;
    }

    /**
     * @param string $type Type of field
     * @return $this
     */
    public function add($type, $name = null, $options = array())
    {
        $index = count($this->fields);
        if (null === $name) {
            $name  = $this->getParameter('mDataProp', $index, $index);
        }

        $field = $this->registry->resolve($type, $name, $this->entity, $options);

        $field->setSearchable($this->getParameter('bSearchable', $index));
        $field->setSearch($this->getParameter('sSearch', $index, ''));

        // FIXME: allow more than one column order
        if ($this->getParameter('iSortCol', 0, -1) == $index) {
            $field->setSort(true);
            $field->setSortDir($this->getParameter('sSortDir', $index));
        }

        if ($this->parent) {
            $this->parent->addField($field);
        } else {
            $this->fields[] = $field;
        }

        return $this;
    }

    /**
     * Returns Datatable instance
     *
     * @return Datatable
     */
    public function getDatatable()
    {
        $datatable = new Datatable($this->em, $this->request);
        $datatable
            ->setEntity($this->entity)
            ->setFields($this->fields)
        ;
        return $datatable;
    }
}