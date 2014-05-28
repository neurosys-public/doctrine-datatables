<?php
namespace NeuroSYS\DoctrineDatatables\Tests;

use NeuroSYS\DoctrineDatatables\Renderer\TwigRenderer;
use NeuroSYS\DoctrineDatatables\TableBuilder;
use NeuroSYS\DoctrineDatatables\Field\Entity;

class BuilderTest extends BaseTestCase
{
    public function testBasics()
    {
        $request  = $this->createSearchRequest(array('iDisplayLength' => 2), array('name', 'price'));
        $builder  = new TableBuilder($this->_em, $request, $this->registry);

        $builder
            ->from('\\NeuroSYS\\DoctrineDatatables\\Tests\\Entity\\Feature', 'f')
            ->join("f.product", "p")
            ->add("text", "f.name")
            ->add("number", "p.name")
        ;

        //echo $builder->getTable()->getResultQueryBuilder()->getQuery()->getDQL();
        //$data = $builder->getTable()->getResultQueryBuilder()->getQuery()->getArrayResult();

        $response = $builder->getTable()
            ->getResponseArray();

        $this->assertEquals(2, count($response['aaData']));
        $this->assertEquals(4, $response['iTotalRecords']);
        $this->assertEquals(4, $response['iTotalDisplayRecords']);

        $this->assertArrayHasKey('id', $response['aaData'][0], 'Result contains a primary key');
    }

    public function testRangeField()
    {
        $request  = $this->createSearchRequest(array(), array('name', 'price'), array('Generation', '10,'));
        $builder  = new TableBuilder($this->_em, $request, $this->registry);

        $builder
            ->from('\\NeuroSYS\\DoctrineDatatables\\Tests\\Entity\\Feature', 'f')
            ->join('f.product', 'p')
            ->add("text", 'f.name')
            ->add("number", 'p.price')
        ;

        $result = $builder->getTable()
            ->getData();

        $this->assertEquals(array(
            array (
                'id' => 1,
                'name' => 'CPU I7 Generation',
                'price' => '1000',
            ),
        ), $result);
    }

    public function testFilterByNonExistentText()
    {
        $request  = $this->createSearchRequest(array(), array('name', 'price'), array('no result please'));
        $builder  = new TableBuilder($this->_em, $request, $this->registry);

        $builder
            ->from('\\NeuroSYS\\DoctrineDatatables\\Tests\\Entity\\Feature', 'f')
            ->join('f.product', 'p')
            ->add("text", 'f.name')
            ->add("number", 'p.price')
        ;

        $result = $builder->getTable()
            ->getResult();

        $this->assertEquals(0, count($result));
    }

    public function testBooleanBield()
    {
        $request  = $this->createSearchRequest(array(), array('name', 'enabled'), array('', '1'));
        $builder  = new TableBuilder($this->_em, $request, $this->registry);

        $builder
            ->from('\\NeuroSYS\\DoctrineDatatables\\Tests\\Entity\\Feature', 'f')
            ->join('f.product', 'p')
            ->add("text", 'f.name')
            ->add("boolean", 'p.enabled')
        ;

        $result = $builder->getTable()
            ->getData();

        $this->assertEquals(3, count($result));

        $request  = $this->createSearchRequest(array(), array('name', 'enabled'), array('', '0'));
        $builder  = new TableBuilder($this->_em, $request, $this->registry);

        $builder
            ->from('\\NeuroSYS\\DoctrineDatatables\\Tests\\Entity\\Feature', 'f')
            ->join("f.product", "p")
            ->add("text", "f.name")
            ->add("boolean", "p.enabled")
        ;

        $result = $builder->getTable()
            ->getResult();

        $this->assertEquals(1, count($result));
    }

    public function testChoiceField()
    {
        $ids = $this->_em->getConnection()->query("SELECT id FROM features")->fetchAll();
        $ids = array_map('current', $ids);

        $request  = $this->createSearchRequest(array(), array('id', 'name'), array(implode(',', $ids)));
        $builder  = new TableBuilder($this->_em, $request, $this->registry);

        $builder
            ->from('\\NeuroSYS\\DoctrineDatatables\\Tests\\Entity\\Feature', 'f')
            ->add("choice", "f.id")
            ->add("text", "f.name")
        ;

        $result = $builder->getTable()
            ->getResult();

        $this->assertEquals(count($ids), count($result));
    }

    public function testMultiFieldWithSort()
    {
        $loader   = new \Twig_Loader_String();
        $renderer = new TwigRenderer(new \Twig_Environment($loader));
        $request  = $this->createSearchRequest(array('sSortDir_0' => 'desc', 'iSortCol_0' => 1), array('price', 'fullName'), array('', 'Laptop'));
        $builder  = new TableBuilder($this->_em, $request, $this->registry, $renderer);

        $builder
            ->from('\\NeuroSYS\\DoctrineDatatables\\Tests\\Entity\\Feature', 'f')
            ->join("f.product", "p")
            ->add("number", "p.price")
            ->add('text', array('p.name', 'f.name'), null, array(
                'template' => '{{ values.product.name }} {{ values.name }}',
            ))
            ->end()
        ;

        $results = $builder->getTable()
            ->getArrayResult();

        foreach ($results as $i => $result) {
            $results[$i] = $result['fullName'];
        }

        // fetch real results
        $sorted = $this->_em->getConnection()->query("SELECT f.id, p.name as p_name, f.name as f_name FROM features f LEFT JOIN products p ON p.id = f.product_id WHERE p.name LIKE '%Laptop%' ORDER BY p.name DESC, f.name DESC")->fetchAll();
        foreach ($sorted as $i => $row) {
            $sorted[$i] = $row['p_name'] . ' ' . $row['f_name'];
        }

        $this->assertEquals($sorted, $results);
    }

    public function testCustomQueryBuilder()
    {
        $request  = $this->createSearchRequest(array(), array('name', 'enabled'), array());

        $qb = $this->_em->createQueryBuilder()
            ->from('\\NeuroSYS\\DoctrineDatatables\\Tests\\Entity\\Product', 'p')
            ->where('p.enabled = 1')
        ;
        $builder  = new TableBuilder($this->_em, $request, $this->registry);
        $builder
            ->setQueryBuilder($qb)
            ->add('text', 'p.name')
            ->add('boolean', 'p.enabled')
        ;

        $results = $builder->getTable()
            ->getData();

        // fetch real results
        $expected = $this->_em->getConnection()->query("SELECT p.id, p.name, p.enabled FROM products p WHERE enabled = 1 ORDER BY p.name ASC")->fetchAll();
        $this->assertEquals($expected, $results);
    }
    public function testRenderer()
    {
        $request  = $this->createSearchRequest(array('sSortDir_0' => 'desc', 'iSortCol_0' => 1), array('price', 'fullName'), array('', 'Laptop'));
        $builder  = new TableBuilder($this->_em, $request, $this->registry, new TwigRenderer(new \Twig_Environment(new \Twig_Loader_String())));

        $builder
            ->from('\\NeuroSYS\\DoctrineDatatables\\Tests\\Entity\\Feature', 'f')
            ->join("f.product", "p")
            ->add("number", "p.price")
            ->add("text", "p.name, f.name", null, array(
                'template' => '{{ values.product.name }} - {{ values.name }}'
            ))
        ;

        $results = $builder->getTable()
            ->getResult();
        foreach ($results as $i => $result) {
            $results[$i] = $result['fullName'];
        }

        // fetch real results
        $sorted = $this->_em->getConnection()->query("SELECT f.id, p.name as p_name, f.name as f_name FROM features f LEFT JOIN products p ON p.id = f.product_id WHERE p.name LIKE '%Laptop%' ORDER BY p.name DESC, f.name DESC")->fetchAll();
        foreach ($sorted as $i => $row) {
            $sorted[$i] = $row['p_name'] . ' - ' . $row['f_name'];
        }

        $this->assertEquals($sorted, $results);
    }

    public function testOneToManyHydrating()
    {
        $request  = $this->createSearchRequest(array('sSortDir_0' => 'desc', 'iSortCol_0' => 1), array('name', 'features'), array('', '1,2'));
        $builder  = new TableBuilder($this->_em, $request, $this->registry, new TwigRenderer(new \Twig_Environment(new \Twig_Loader_String())));

        $builder
            ->from('\\NeuroSYS\\DoctrineDatatables\\Tests\\Entity\\Product', 'p')
            ->join("p.features", "f")
            ->add("text", "p.name")
            ->add( "choice", "f.name", "f.id", array(
                'template' => '{% for f in values.features %}{{ f.name }},{% endfor %}'
            ))
        ;

        //echo($builder->getTable()->getResultQueryBuilder()->getDQL());

        $results = $builder->getTable()->getData();

        $this->assertNotEmpty($results);
        $this->assertEquals('SolidState drive,CPU I7 Generation,', $results[0]['features']);
    }
}
