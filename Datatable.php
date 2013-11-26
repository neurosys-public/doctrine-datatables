<?php
namespace NeuroSYS\DoctrineDatatables;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use NeuroSYS\DoctrineDatatables\Field\Entity;
use NeuroSYS\DoctrineDatatables\Field\AbstractField;
use NeuroSYS\DoctrineDatatables\Field\MultiField;

class Datatable extends MultiField
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Entity
     */
    private $entity;

    /**
     * @var int
     */
    private $maxResults;

    /**
     * @var int
     */
    private $firstResult;

    /**
     * @var array
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
     * Constructor
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, array $request)
    {
        $this->em      = $em;
        $this->request = $request;

        $this->setMaxResults($request['iDisplayLength']);
        $this->setFirstResult($request['iDisplayStart']);
    }

    /**
     * @param Entity $entity
     */
    public function setEntity(Entity $entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return Entity
     */
    public function getEntity()
    {
        return $this->entity;
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
        return clone $this->queryBuilder;
    }

    /**
     * @return QueryBuilder
     */
    public function getResultQueryBuilder()
    {
        $qb = $this->getQueryBuilder();

        $this
            ->select($qb)
            ->from($qb)
            ->join($qb)
            ->filter($qb)
            ->limit($qb)
            ->offset($qb)
            ->order($qb);

        return $qb;
    }

    public function getResult()
    {
        $results = $this->getResultQueryBuilder()->getQuery()->getArrayResult();
        foreach ($results as $i => $result) {
            for ($j = 0; true; $j++) {
                if (!array_key_exists($j, $result)) {
                    break;
                }
                $result = array_merge($result, $result[$j]);
                unset($result[$j]);
            }
            $results[$i] = $this->format($result);
        }
        return $results;
    }

    /**
     * @param array $values
     * @return array
     */
    public function format(array $values)
    {
        if ($this->formatter) {
            return call_user_func_array($this->formatter, array($this, $values));
        }
        foreach ($this->getFields() as $field) {
            // get field value by reference
            $value = &$field->getValue($values);
            // set formatted value
            $value = $field->format($values);
        }
        return $values;
    }

    public function getPath()
    {
        return array();
    }

    /**
     * @return int Total query results before searches/filtering
     */
    public function getCountAllResults()
    {
        $rootEntityIdentifier = 'id'; // FIXME: fetch it from Metadata

        $qb = $this->getQueryBuilder()
            ->select('COUNT(' . $this->getEntity()->getAlias() . '.' . $rootEntityIdentifier . ')');
        $this
            ->from($qb)
            ->join($qb)
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return int Total query results after searches/filtering
     */
    public function getCountFilteredResults()
    {
        $rootEntityIdentifier = 'id'; // FIXME: fetch it from Metadata
        $qb = $this->getQueryBuilder()
            ->select('COUNT(DISTINCT ' . $this->getEntity()->getAlias() . '.' . $rootEntityIdentifier . ')');

        $this
            ->from($qb)
            ->join($qb)
            ->filter($qb)
            ->limit($qb)
            ->offset($qb)
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function getResponseArray()
    {
        return array(
            'sEcho'                => $this->request['sEcho'],
            'aaData'               => $this->getResult(),
            "iTotalRecords"        => $this->getCountAllResults(),
            "iTotalDisplayRecords" => $this->getCountFilteredResults()
        );
    }

    /**
     * @param QueryBuilder $qb
     * @return Datatable
     */
    public function from(QueryBuilder $qb)
    {
        if (!$qb->getRootEntities()) {
            $qb->from($this->getEntity()->getClassName(), $this->getEntity()->getAlias());
        }

        return $this;
    }

    /**
     * @param QueryBuilder $qb
     * @return Datatable
     */
    public function filter(QueryBuilder $qb)
    {
        $andx = $qb->expr()->andX();
        foreach ($this->fields as $field) {
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
     * @return Datatable
     */
    public function select(QueryBuilder $qb)
    {
        // TODO: fetch primary key name from metadata
        $partials[$this->getEntity()->getAlias()] = array('id');

        $this->addPartials($partials, $qb);

        foreach ($partials as $alias => $partial) {
            $qb->addSelect('partial ' . $alias . '.{' . implode(',', $partial) . '}');
        }
        return $this;
    }

    /**
     * @param QueryBuilder $qb
     * @return Datatable
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

    public function order(QueryBuilder $qb, $dir = 'asc')
    {
        foreach ($this->orders as $order) {
            $this->fields[$order['index']]->order($qb, $order['dir']);
        }

        return $this;
    }
}