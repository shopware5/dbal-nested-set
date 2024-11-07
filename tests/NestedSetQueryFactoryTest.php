<?php

declare(strict_types=1);

namespace Shopware\DbalNestedSetTest;

use PHPUnit\Framework\TestCase;
use Shopware\DbalNestedSet\NestedSetConfig;
use Shopware\DbalNestedSet\NestedSetFactory;
use Shopware\DbalNestedSet\NestedSetQueryFactory;

class NestedSetQueryFactoryTest extends TestCase
{
    /**
     * @var NestedSetQueryFactory
     */
    private $queryFactory;

    protected function setUp(): void
    {
        $connection = \NestedSetBootstrap::getConnection();
        \NestedSetBootstrap::importTable();
        \NestedSetBootstrap::insertDemoTree();
        \NestedSetBootstrap::insertDemoTree(2);
        $this->queryFactory = NestedSetFactory::createQueryFactory($connection, new NestedSetConfig('id', 'left', 'right', 'level'));
    }

    public function testFetchAllChildren(): void
    {
        $qb = $this->queryFactory->createChildrenQueryBuilder('tree', 't', 'root_id', 2)
            ->select('*');

        $sql = $qb->getSQL();

        static::assertStringContainsString('tree', $sql);
        static::assertStringContainsString('t.', $sql);

        $rows = $qb->execute()->fetchAll();

        static::assertCount(1, $rows);
        static::assertEquals('Suits', $rows[0]['name']);
    }

    public function testFetchAllChildrenAndNode(): void
    {
        $qb = $this->queryFactory->createParentAndChildrenQueryBuilder('tree', 't', 'root_id', 24)
            ->select('*');

        $sql = $qb->getSQL();

        static::assertStringContainsString('tree', $sql);
        static::assertStringContainsString('t.', $sql);

        $rows = $qb->execute()->fetchAll();

        static::assertCount(3, $rows);
        static::assertEquals('Suits', $rows[0]['name']);
    }

    public function testFetchSubtree(): void
    {
        $qb = $this->queryFactory->createSubtreeQueryBuilder('tree', 't', 'root_id', 2)
            ->select('*');

        $sql = $qb->getSQL();

        static::assertStringContainsString('tree', $sql);
        static::assertStringContainsString('t.', $sql);

        $rows = $qb->execute()->fetchAll();

        static::assertCount(3, $rows);
        static::assertEquals('Suits', $rows[0]['name']);
        static::assertEquals('Slacks', $rows[1]['name']);
        static::assertEquals('Jackets', $rows[2]['name']);
    }

    public function testFetchParents(): void
    {
        $qb = $this->queryFactory->createParentsQueryBuilder('tree', 't', 'root_id', 2)
            ->select('*');

        $sql = $qb->getSQL();
        static::assertStringContainsString('tree', $sql);
        static::assertStringContainsString('t.', $sql);

        $rows = $qb->execute()->fetchAll();

        static::assertCount(1, $rows);
        static::assertEquals('Clothing', $rows[0]['name']);
    }

    public function testFetchParentsOnLeaf(): void
    {
        $qb = $this->queryFactory->createParentsQueryBuilder('tree', 't', 'root_id', 6)
            ->select('*');

        $sql = $qb->getSQL();
        static::assertStringContainsString('tree', $sql);
        static::assertStringContainsString('t.', $sql);

        $rows = $qb->execute()->fetchAll();

        static::assertCount(3, $rows);
        static::assertEquals('Suits', $rows[0]['name']);
        static::assertEquals('Mens', $rows[1]['name']);
        static::assertEquals('Clothing', $rows[2]['name']);
    }

    public function testFetchAllRoots(): void
    {
        $qb = $this->queryFactory->createFetchRootsQueryBuilder('tree', 't')
            ->select('*');

        $sql = $qb->getSQL();
        static::assertStringContainsString('tree', $sql);
        static::assertStringContainsString('t.', $sql);

        $rows = $qb->execute()->fetchAll();

        static::assertCount(2, $rows);
        static::assertEquals('Clothing', $rows[0]['name']);
        static::assertEquals('Clothing', $rows[1]['name']);
    }

    public function testFetchSubtreeWithRootOnlySelected(): void
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

    public function testFetchSubtreeWithASingleSelectedNodeSlacks(): void
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

    public function testFetchSubtreeWithSelectedNodesMensAndDresses(): void
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

    public function testFetchSubtreeWithSelectedNodesMensAndWomen(): void
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

    public function testFetchSubtreeWithSelectedNodesWithATwoAsADepthParameter(): void
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

    public function testFetchSubtreeWithSelectedNodesWithAZeroDepthParameter(): void
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
        $names = \array_map(function (array $node) {
            return $node['name'];
        }, $rows);

        static::assertEquals($expectedNames, $names, 'Got: ' . \print_r($names, true) . "\n and expected: " . \print_r($expectedNames, true));
    }
}
