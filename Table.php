<?php
namespace NeuroSYS\DoctrineDatatables;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use NeuroSYS\DoctrineDatatables\Field\Entity;
use NeuroSYS\DoctrineDatatables\Field\AbstractField;
use NeuroSYS\DoctrineDatatables\Renderer\PhpRenderer;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class Table extends Entity
{
    const HYDRATE_ARRAY  = 'array';
    const HYDRATE_ENTITY = 'entity';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var int
     */
    private $maxResults;

    /**
     * @var int
     */
    private $firstResult;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var array
     */
    private $orders = array();

    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @var string One of 'array' or 'entity'
     */
    private $hydrate = 'entity';

    /**
     * @var Entity[]
     */
    private $entities = array();

    /**
     * Constructor
     *
     * @param EntityManager $em
     */
    public function __construct($name, $alias, EntityManager $em, Request $request, RendererInterface $renderer = null)
    {
        if (!$alias) {
            throw new \Exception("Alias is required");
        }
        parent::__construct($this, $name, $alias);

        if (!$renderer) {
            $renderer = new PhpRenderer();
        }
        $this->entities[$alias] = $this;
        $this->em       = $em;
        $this->request  = $request;
        $this->setRenderer($renderer);
        $this->setMaxResults($request->get('iDisplayLength'));
        $this->setFirstResult($request->get('iDisplayStart'));
    }

    public function addEntity(Entity $entity)
    {
        $this->entities[$entity->getAlias()] = $entity;
    }

    /**
     * @param $alias
     * @return Entity|null
     */
    public function getEntity($alias)
    {
        if ($this->getAlias() == $alias) {
            return $this;
        }
        if (isset($this->entities[$alias])) {
            return $this->entities[$alias];
        }
        return null;
    }

    public function setRenderer(RendererInterface $renderer = null)
    {
        $this->renderer = $renderer;
    }

    /**
     * @param array[] $orders
     */
    public function setOrders(array $orders)
    {
        $this->orders = $orders;

        return $this;
    }

    /**
     * @param $maxResults
     * @return $this
     */
    public function setMaxResults($maxResults)
    {
        $this->maxResults = $maxResults;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxResults()
    {
        return $this->maxResults;
    }

    /**
     * @param $firstResult
     * @return $this
     */
    public function setFirstResult($firstResult)
    {
        $this->firstResult = $firstResult;

        return $this;
    }

    /**
     * @return int
     */
    public function getFirstResult()
    {
        return $this->firstResult;
    }

    /**
     * @param QueryBuilder $qb
     * @return $this
     */
    public function setQueryBuilder(QueryBuilder $qb)
    {
        $this->queryBuilder = $qb;

        return $this;
    }

    /**
     * @return QueryBuilder Original query builder clone
     */
    public function getQueryBuilder()
    {
        if (!$this->queryBuilder) {
            $this->queryBuilder = $this->em->createQueryBuilder();
        }
        return $this->queryBuilder;
    }

    /**
     * @return RendererInterface
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    public function getResult()
    {
        return $this->getData('entity');
    }

    public function getArrayResult()
    {
        return $this->getData('array');
    }

    public function getData($hydrate = 'array')
    {
        $query = $this->getResultQueryBuilder()->getQuery();
        if ($hydrate == 'array') {
            $results = $query->getArrayResult();
        } else {
            $results = $query->getResult();
        }

        foreach ($results as $i => $result) {
            $results[$i] = $this->formatResult($result);
        }
        return $results;
    }

    /**
     * @param mixed $values
     * @return array
     */
    public function formatResult($values)
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        $results = array();
        foreach ($this->getPrimaryKeys() as $key) {
            $results[$key] = $this->getValue($values, $key, $accessor);
        }
        foreach ($this->getFields() as $name => $field) {

            if ($context = $field->getContext()) {
                $value = $this->getValue($values, $context, $accessor);
            } else {
                $value = array();
                $selectPaths = $field->getSelectPaths();
                if (count($selectPaths) == 1) {
                    try {
                        foreach ($field->getSelectPaths() as $selectPath) {
                            $value[] = $this->getValue($values, implode('.', $selectPath), $accessor);
                        }
                    } catch (\Exception $ex) {
                        $value = $values;
                    }
                } else {
                    $value = $values;
                }
                if (is_array($value) && count($value) == 1) {
                    $value = $value[0];
                }
            }
            $results[$name] = $field->format($values, $value);
        }
        return $results;
    }

    public function getManager()
    {
        return $this->em;
    }


    protected function addJoin(QueryBuilder $qb)
    {
        foreach ($this->entities as $entity) {
            if ($entity != $this) {
                $qb->leftJoin($entity->getFullName(), $entity->getAlias());
            }
        }
        return $this;
    }

    /**
     * @return array Field path
     */
    public function getPath()
    {
        // root entity has empty path
        return array();
    }

    protected function getValue($object, $path, $accessor)
    {
        if (is_array($object)) {
            $path = '['.str_replace('.', '][', $path).']';
        }
        return $accessor->getValue($object, $path);
    }

    /**
     * @return QueryBuilder
     */
    public function getResultQueryBuilder()
    {
        $qb = clone $this->getQueryBuilder();

        $this
            ->select($qb)
            ->addFrom($qb)
            ->addJoin($qb)
            ->addFilter($qb)
            ->limit($qb)
            ->offset($qb)
            ->addOrder($qb);

        return $qb;
    }

    /**
     * @return int Total query results before searches/filtering
     */
    public function getCountAllResults()
    {
        $rootEntityIdentifier = 'id'; // FIXME: fetch it from Metadata

        $qb = clone $this->getQueryBuilder();
        $qb->select('COUNT(DISTINCT ' . $this->getAlias() . '.' . $rootEntityIdentifier . ')');
        $this
            ->addFrom($qb)
            ->addJoin($qb)
        ;
        $qb->resetDQLPart('groupBy');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return int Total query results after searches/filtering
     */
    public function getCountFilteredResults()
    {
        $rootEntityIdentifier = 'id'; // FIXME: fetch it from Metadata

        $qb = clone $this->getQueryBuilder();
        $qb->select('COUNT(DISTINCT ' . $this->getAlias() . '.' . $rootEntityIdentifier . ')');
        $this
            ->addFrom($qb)
            ->addJoin($qb)
            ->addFilter($qb)
        ;
        $qb->resetDQLPart('groupBy');
        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function getResponseArray()
    {
        return array(
            'sEcho'                => $this->request->get('sEcho'),
            'aaData'               => $this->getData($this->hydrate),
            "iTotalRecords"        => $this->getCountAllResults(),
            "iTotalDisplayRecords" => $this->getCountFilteredResults()
        );
    }

    /**
     * @param QueryBuilder $qb
     * @return Table
     */
    public function from($className, $alias)
    {
        $this->setName($className);
        $this->setAlias($alias);

        return $this;
    }

    /**
     * @param QueryBuilder $qb
     * @return Table
     */
    protected function addFrom(QueryBuilder $qb)
    {
        if (count($qb->getDQLPart('from')) == 0) {
            $qb->from($this->getName(), $this->getAlias());
        }

        return $this;
    }

    /**
     * @param QueryBuilder $qb
     * @return Table
     */
    protected function addFilter(QueryBuilder $qb)
    {
        $andx = $qb->expr()->andX();
        foreach ($this->getFields() as $field) {
            if ($field->isSearch()) {
                $andx->add($field->filter($qb));
            }
        }
        if ($andx->count() > 0) {
            $qb->andWhere($andx);
        }
        return $this;
    }

    /**
     * @param QueryBuilder $qb
     * @return Table
     */
    public function select(QueryBuilder $qb)
    {
        $select = $this->getSelect();
        foreach ($select as $alias => $names) {
            if (!is_array($names)) {
                $names = array($names);
            }
            array_unshift($names, 'id');
            $qb->addSelect('partial ' . $alias . '.{' . implode(',', array_unique($names)) . '}');
            //$qb->addSelect($alias);
        }

        return $this;
    }


    public function getTable()
    {
        return $this;
    }

    /**
     * @param QueryBuilder $qb
     * @return Table
     */
    protected function limit(QueryBuilder $qb)
    {
        $qb->setMaxResults($this->getMaxResults());

        return $this;
    }

    protected function offset(QueryBuilder $qb)
    {
        $qb->setFirstResult($this->getFirstResult());

        return $this;
    }

    public function addOrder(QueryBuilder $qb)
    {
        foreach ($this->getFields() as $field) {
            if ($field->isSortable()) {
                $field->order($qb);
            }
        }

        return $this;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function __toString()
    {
        $str = '[';
        foreach ($this->getFields() as $field) {
            $str .= $field . "\n";
        }
        return $str . "]";
    }
}