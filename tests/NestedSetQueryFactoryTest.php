<?php declare(strict_types=1);

namespace Shopware\DbalNestedSetTest;

use PHPUnit\Framework\TestCase;
use Shopware\DbalNestedSet\NestedSetConventionsConfig;
use Shopware\DbalNestedSet\NestedSetFactory;
use Shopware\DbalNestedSet\NestedSetQueryFactory;

class NestedSetQueryFactoryTest extends TestCase
{
    /**
     * @var NestedSetQueryFactory
     */
    private $nestedSet;

    public function setUp()
    {
        $connection = \NestedSetBootstrap::getConnection();
        $connection->exec(file_get_contents(__DIR__ . '/fixtures.sql'));
        \NestedSetBootstrap::insertDemoTree();
        $this->nestedSet = NestedSetFactory::createQueryFactory($connection, new NestedSetConventionsConfig('id', 'left', 'right', 'level'));
    }

    public function test_fetch_all_children()
    {
        $qb = $this->nestedSet->createChildrenQueryBuilder('tree', 't', 'root_id', 2)
            ->select('*');

        $sql = $qb->getSQL();

        $this->assertContains('tree', $sql);
        $this->assertContains('t.', $sql);

        $rows = $qb->execute()->fetchAll();

        $this->assertCount(1, $rows);
        $this->assertEquals('Suits', $rows[0]['name']);
    }

    public function test_fetch_subtree()
    {
        $qb = $this->nestedSet->createSubtreeQueryBuilder('tree', 't', 'root_id', 2)
            ->select('*');

        $sql = $qb->getSQL();

        $this->assertContains('tree', $sql);
        $this->assertContains('t.', $sql);

        $rows = $qb->execute()->fetchAll();

        $this->assertCount(3, $rows);
        $this->assertEquals('Suits', $rows[0]['name']);
        $this->assertEquals('Slacks', $rows[1]['name']);
        $this->assertEquals('Jackets', $rows[2]['name']);
    }

    public function test_fetch_parents()
    {
        $qb = $this->nestedSet->createParentsQueryBuilder('tree', 't', 'root_id', 2)
            ->select('*');

        $sql = $qb->getSQL();
        $this->assertContains('tree', $sql);
        $this->assertContains('t.', $sql);

        $rows = $qb->execute()->fetchAll();

        $this->assertCount(1, $rows);
        $this->assertEquals('Clothing', $rows[0]['name']);
    }

    public function test_fetch_parents_on_leaf()
    {
        $qb = $this->nestedSet->createParentsQueryBuilder('tree', 't', 'root_id', 6)
            ->select('*');

        $sql = $qb->getSQL();
        $this->assertContains('tree', $sql);
        $this->assertContains('t.', $sql);

        $rows = $qb->execute()->fetchAll();

        $this->assertCount(3, $rows);
        $this->assertEquals('Suits', $rows[0]['name']);
        $this->assertEquals('Mens', $rows[1]['name']);
        $this->assertEquals('Clothing', $rows[2]['name']);
    }

    public function test_fetch_all_roots()
    {
        $qb = $this->nestedSet->createFetchRootsQueryBuilder('tree', 't')
            ->select('*');

        $sql = $qb->getSQL();
        $this->assertContains('tree', $sql);
        $this->assertContains('t.', $sql);

        $rows = $qb->execute()->fetchAll();

        $this->assertCount(1, $rows);
        $this->assertEquals('Clothing', $rows[0]['name']);
    }
}