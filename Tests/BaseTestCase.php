<?php
namespace NeuroSYS\DoctrineDatatables\Tests;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

use NeuroSYS\DoctrineDatatables\FieldRegistry;

class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManager
     */
    protected $_em;

    protected $registry;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->_em = self::createTestEntityManager();
        $this->registry = new FieldRegistry();
        $this->_createSchemas();
        $this->_insertData();
    }

    public function createSearchRequest($override = array(), $props = array(), $search = array())
    {
        $request = array(
            'sEcho' => '1',
            'iColumns' => count($props),
            'sColumns' => '',
            'iDisplayStart' => '0',
            'iDisplayLength' => '10',
            'sSearch' => '',
            'bRegex' => 'false',
            'iSortingCols' => '1',
            'iSortCol_0' => '0',
            'sSortDir_0' => 'asc',
        );
        foreach ($props as $i => $prop) {
            $request['mDataProp_' . $i]   = $prop;
            $request['sSearch_' . $i]     = isset($search[$i]) ? $search[$i] : '';
            $request['bRegex_' . $i]      = 'false';
            $request['bSearchable_' . $i] = 'true';
            $request['bSortable_' . $i]   = 'true';
        }

        return array_merge($request, $override);
    }

    /**
     * @return EntityManager
     */
    public static function createTestEntityManager($paths = array())
    {
        if (!class_exists('PDO') || !in_array('sqlite', \PDO::getAvailableDrivers())) {
            self::markTestSkipped('This test requires SQLite support in your environment');
        }
        $paths  = array(realpath(__DIR__ . '/Entity'));
        $config = Setup::createAnnotationMetadataConfiguration($paths, false);
        $params = array(
            'driver'   => 'pdo_sqlite',
            'memory'   => true,
            'password' => '',
            'dbname'   => 'neurosys'
        );

        return EntityManager::create($params, $config);
    }

    /**
     * create schema from annotation mapping files
     * @return void
     */
    protected function _createSchemas()
    {
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->_em);
        $classes    = array(
            $this->_em->getClassMetadata("\\NeuroSYS\\DoctrineDatatables\\Tests\\Entity\\Product"),
            $this->_em->getClassMetadata("\\NeuroSYS\\DoctrineDatatables\\Tests\\Entity\\Feature"),
        );
        $schemaTool->dropSchema($classes);

        $schemaTool->createSchema($classes);
    }

    protected function _insertData()
    {
        $em = $this->_em;
        $p  = new Entity\Product;
        $p->setName('Laptop')
            ->setPrice(1000)
            ->setDescription('New laptop')
            ->setEnabled(true);
        $p2  = new Entity\Product;
        $p2->setName('PC')
            ->setPrice(600)
            ->setDescription('Old good PC')
            ->setEnabled(false);
        $f  = new Entity\Feature;
        $f->setName('CPU I7 Generation')
            ->setProduct($p);
        $f1 = new Entity\Feature;
        $f1->setName('SolidState drive')
            ->setProduct($p);
        $f2 = new Entity\Feature;
        $f2->setName('SLI graphic card')
            ->setProduct($p);

        $f3 = new Entity\Feature;
        $f3->setName('G-Force graphic card')
            ->setProduct($p2);
        $em->persist($p);
        $em->persist($p2);
        $em->persist($f);
        $em->persist($f1);
        $em->persist($f2);
        $em->persist($f3);
        $em->flush();
    }

}
