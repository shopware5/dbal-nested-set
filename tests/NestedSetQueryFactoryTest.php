<?php declare(strict_types=1);

namespace Shopware\DbalNestedSetTest;

use NestedSetBootstrap;
use PHPUnit\Framework\TestCase;
use Shopware\DbalNestedSet\NestedSetConfig;
use Shopware\DbalNestedSet\NestedSetFactory;
use Shopware\DbalNestedSet\NestedSetQueryFactory;
use function array_map;
use function print_r;

class NestedSetQueryFactoryTest extends TestCase
{
    /**
     * @var NestedSetQueryFactory
     */
    private $queryFactory;

    public function setUp(): void
    {
        $connection = NestedSetBootstrap::getConnection();
        NestedSetBootstrap::importTable();
        NestedSetBootstrap::insertDemoTree();
        NestedSetBootstrap::insertDemoTree(2);
        $this->queryFactory = NestedSetFactory::createQueryFactory($connection, new NestedSetConfig('id', 'left', 'right', 'level'));
    }

    public function test_fetch_all_children(): void
    {
        $qb = $this->queryFactory->createChildrenQueryBuilder('tree', 't', 'root_id', 2)
            ->select('*');

        $sql = $qb->getSQL();

        self::assertStringContainsString('tree', $sql);
        self::assertStringContainsString('t.', $sql);

        $rows = $qb->execute()->fetchAll();

        self::assertCount(1, $rows);
        self::assertEquals('Suits', $rows[0]['name']);
    }

    public function test_fetch_all_children_and_node(): void
    {
        $qb = $this->queryFactory->createParentAndChildrenQueryBuilder('tree', 't', 'root_id', 24)
            ->select('*');

        $sql = $qb->getSQL();

        self::assertStringContainsString('tree', $sql);
        self::assertStringContainsString('t.', $sql);

        $rows = $qb->execute()->fetchAll();

        self::assertCount(3, $rows);
        self::assertEquals('Suits', $rows[0]['name']);
    }

    public function test_fetch_subtree(): void
    {
        $qb = $this->queryFactory->createSubtreeQueryBuilder('tree', 't', 'root_id', 2)
            ->select('*');

        $sql = $qb->getSQL();

        self::assertStringContainsString('tree', $sql);
        self::assertStringContainsString('t.', $sql);

        $rows = $qb->execute()->fetchAll();

        self::assertCount(3, $rows);
        self::assertEquals('Suits', $rows[0]['name']);
        self::assertEquals('Slacks', $rows[1]['name']);
        self::assertEquals('Jackets', $rows[2]['name']);
    }

    public function test_fetch_parents(): void
    {
        $qb = $this->queryFactory->createParentsQueryBuilder('tree', 't', 'root_id', 2)
            ->select('*');

        $sql = $qb->getSQL();
        self::assertStringContainsString('tree', $sql);
        self::assertStringContainsString('t.', $sql);

        $rows = $qb->execute()->fetchAll();

        self::assertCount(1, $rows);
        self::assertEquals('Clothing', $rows[0]['name']);
    }

    public function test_fetch_parents_on_leaf(): void
    {
        $qb = $this->queryFactory->createParentsQueryBuilder('tree', 't', 'root_id', 6)
            ->select('*');

        $sql = $qb->getSQL();
        self::assertStringContainsString('tree', $sql);
        self::assertStringContainsString('t.', $sql);

        $rows = $qb->execute()->fetchAll();

        self::assertCount(3, $rows);
        self::assertEquals('Suits', $rows[0]['name']);
        self::assertEquals('Mens', $rows[1]['name']);
        self::assertEquals('Clothing', $rows[2]['name']);
    }

    public function test_fetch_all_roots(): void
    {
        $qb = $this->queryFactory->createFetchRootsQueryBuilder('tree', 't')
            ->select('*');

        $sql = $qb->getSQL();
        self::assertStringContainsString('tree', $sql);
        self::assertStringContainsString('t.', $sql);

        $rows = $qb->execute()->fetchAll();

        self::assertCount(2, $rows);
        self::assertEquals('Clothing', $rows[0]['name']);
        self::assertEquals('Clothing', $rows[1]['name']);
    }

    public function test_fetch_subtree_with_root_only_selected(): void
    {
        $qb = $this->queryFactory
            ->createSubtreeThroughMultipleNodesQueryBuilder('tree', 't', 'root_id', [1])
            ->select('*');

        $this->assertSubTree(
            [
                'Clothing',
                'Mens',
                'Women',
            ],
            $qb->execute()->fetchAll()
        );
    }

    public function test_fetch_subtree_with_a_single_selected_node_slacks(): void
    {
        $qb = $this->queryFactory
            ->createSubtreeThroughMultipleNodesQueryBuilder('tree', 't', 'root_id', [5])
            ->select('*');

        $this->assertSubTree(
            [
                'Clothing',
                'Mens',
                'Suits',
                'Slacks',
                'Jackets',
                'Women',
            ],
            $qb->execute()->fetchAll()
        );
    }

    public function test_fetch_subtree_with_selected_nodes_mens_and_dresses(): void
    {
        $qb = $this->queryFactory
            ->createSubtreeThroughMultipleNodesQueryBuilder('tree', 't', 'root_id', [2, 7])
            ->select('*');

        $this->assertSubTree(
            [
                'Clothing',
                'Mens',
                'Suits',
                'Women',
                'Dresses',
                'Evening Growns',
                'Sun Dresses',
                'Skirts',
                'Blouses',
            ],
            $qb->execute()->fetchAll()
        );
    }

    public function test_fetch_subtree_with_selected_nodes_mens_and_women(): void
    {
        $qb = $this->queryFactory
            ->createSubtreeThroughMultipleNodesQueryBuilder('tree', 't', 'root_id', [3, 2])
            ->select('*');

        $this->assertSubTree(
            [
                'Clothing',
                'Mens',
                'Suits',
                'Women',
                'Dresses',
                'Skirts',
                'Blouses',
            ],
            $qb->execute()->fetchAll()
        );
    }

    public function test_fetch_subtree_with_selected_nodes_with_a_two_as_a_depth_parameter(): void
    {
        $qb = $this->queryFactory
            ->createSubtreeThroughMultipleNodesQueryBuilder('tree', 't', 'root_id', [2, 3], 2)
            ->select('*');

        $this->assertSubTree(
            [
                'Clothing',
                'Mens',
                'Suits',
                'Slacks',
                'Jackets',
                'Women',
                'Dresses',
                'Evening Growns',
                'Sun Dresses',
                'Skirts',
                'Blouses',
            ],
            $qb->execute()->fetchAll()
        );
    }

    public function test_fetch_subtree_with_selected_nodes_with_a_zero_depth_parameter(): void
    {
        $qb = $this->queryFactory
            ->createSubtreeThroughMultipleNodesQueryBuilder('tree', 't', 'root_id', [3, 2], 0)
            ->select('*');

        $this->assertSubTree(
            [
                'Clothing',
                'Mens',
                'Women',
            ],
            $qb->execute()->fetchAll()
        );
    }

    private function assertSubTree(array $expectedNames, array $rows): void
    {
        $names = array_map(function (array $node) {
            return $node['name'];
        }, $rows);

        self::assertEquals($expectedNames, $names, 'Got: ' . print_r($names, true) . "\n and expected: " . print_r($expectedNames, true));
    }
}
