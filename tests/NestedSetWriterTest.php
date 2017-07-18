<?php declare(strict_types=1);

namespace Shopware\DbalNestedSetTest;

use PHPUnit\Framework\TestCase;
use Shopware\DbalNestedSet\InvalidNodeOperationException;
use Shopware\DbalNestedSet\NestedSetConventionsConfig;
use Shopware\DbalNestedSet\NestedSetFactory;
use Shopware\DbalNestedSet\NestedSetReader;
use Shopware\DbalNestedSet\NestedSetWriter;

class NestedSetWriterTest extends TestCase
{
    /**
     * @var NestedSetWriter
     */
    private $nestedSet;

    /**
     * @var NestedSetReader
     */
    private $reader;

    public function setUp()
    {
        $connection = \NestedSetBootstrap::getConnection();
        $connection->exec(file_get_contents(__DIR__ . '/fixtures.sql'));
        $this->nestedSet = NestedSetFactory::createWriter($connection, new NestedSetConventionsConfig('id', 'left', 'right', 'level'));
        $this->reader = NestedSetFactory::createReader($connection, new NestedSetConventionsConfig('id', 'left', 'right', 'level'));
    }

    public function test_insertAsLastChild_by_recreating_the_demo_tree()
    {
        $nestedSet = $this->nestedSet;

        $nestedSet->insertRoot('tree', 'root_id', 100, ['name' => 'Clothing']);
        $nestedSet->insertAsLastChild('tree', 'root_id', 1, ['name' => 'Men']);
        $nestedSet->insertAsLastChild('tree', 'root_id', 1, ['name' => 'Women']);
        $nestedSet->insertAsLastChild('tree', 'root_id', 2, ['name' => 'Suits']);
        $nestedSet->insertAsLastChild('tree', 'root_id', 3, ['name' => 'Dresses']);
        $nestedSet->insertAsLastChild('tree', 'root_id', 3, ['name' => 'Skirts']);
        $nestedSet->insertAsLastChild('tree', 'root_id', 3, ['name' => 'Blouses']);
        $nestedSet->insertAsLastChild('tree', 'root_id', 4, ['name' => 'Slacks']);
        $nestedSet->insertAsLastChild('tree', 'root_id', 4, ['name' => 'Jackets']);
        $nestedSet->insertAsLastChild('tree', 'root_id', 5, ['name' => 'Evening Gowns']);
        $nestedSet->insertAsLastChild('tree', 'root_id', 5, ['name' => 'Sun Dresses']);
        \NestedSetBootstrap::validateTree(100);

        $this->assertNode(1, 1, 22, 0, 100);
        $this->assertNode(2, 2, 9, 1, 100);
        $this->assertNode(4, 3, 8, 2, 100);
        $this->assertNode(8, 4, 5, 3, 100);
        $this->assertNode(9, 6, 7, 3, 100);
        $this->assertNode(3, 10, 21, 1, 100);
        $this->assertNode(5, 11, 16, 2, 100);
        $this->assertNode(10, 12, 13, 3, 100);
        $this->assertNode(11, 14, 15, 3, 100);
        $this->assertNode(6, 17, 18, 2, 100);
        $this->assertNode(7, 19, 20, 2, 100);
    }

    public function test_insertAsFirstChild_by_recreating_the_demo_tree()
    {
        $nestedSet = $this->nestedSet;

        $nestedSet->insertRoot('tree', 'root_id', 100, ['name' => 'Clothing']);
        $nestedSet->insertAsFirstChild('tree', 'root_id', 1, ['name' => 'Women']);
        $nestedSet->insertAsFirstChild('tree', 'root_id', 1, ['name' => 'Men']);
        $nestedSet->insertAsFirstChild('tree', 'root_id', 3, ['name' => 'Suits']);
        $nestedSet->insertAsFirstChild('tree', 'root_id', 2, ['name' => 'Blouses']);
        $nestedSet->insertAsFirstChild('tree', 'root_id', 2, ['name' => 'Skirts']);
        $nestedSet->insertAsFirstChild('tree', 'root_id', 2, ['name' => 'Dresses']);
        $nestedSet->insertAsFirstChild('tree', 'root_id', 4, ['name' => 'Jackets']);
        $nestedSet->insertAsFirstChild('tree', 'root_id', 4, ['name' => 'Slacks']);
        $nestedSet->insertAsFirstChild('tree', 'root_id', 7, ['name' => 'Sun Dresses']);
        $nestedSet->insertAsFirstChild('tree', 'root_id', 7, ['name' => 'Evening Gowns']);
        \NestedSetBootstrap::validateTree(100);

        $this->assertNode(1, 1, 22, 0, 100);
        $this->assertNode(3, 2, 9, 1, 100);
        $this->assertNode(4, 3, 8, 2, 100);
        $this->assertNode(9, 4, 5, 3, 100);
        $this->assertNode(8, 6, 7, 3, 100);
        $this->assertNode(2, 10, 21, 1, 100);
        $this->assertNode(7, 11, 16, 2, 100);
        $this->assertNode(11, 12, 13, 3, 100);
        $this->assertNode(10, 14, 15, 3, 100);
        $this->assertNode(6, 17, 18, 2, 100);
        $this->assertNode(5, 19, 20, 2, 100);
    }

    public function test_insertAsNextSibling_by_recreating_the_demo_tree_using_insert_as_fist_child_where_necessary()
    {
        $nestedSet = $this->nestedSet;

        $nestedSet->insertRoot('tree', 'root_id', 100, ['name' => 'Clothing']);
        $nestedSet->insertAsFirstChild('tree', 'root_id', 1, ['name' => 'Men']);
        $nestedSet->insertAsNextSibling('tree', 'root_id', 2, ['name' => 'Women']);
        $nestedSet->insertAsFirstChild('tree', 'root_id', 2, ['name' => 'Suits']);
        $nestedSet->insertAsFirstChild('tree', 'root_id', 3, ['name' => 'Dresses']);
        $nestedSet->insertAsNextSibling('tree', 'root_id', 5, ['name' => 'Skirts']);
        $nestedSet->insertAsNextSibling('tree', 'root_id', 6, ['name' => 'Blouses']);
        $nestedSet->insertAsFirstChild('tree', 'root_id', 4, ['name' => 'Jackets']);
        $nestedSet->insertAsFirstChild('tree', 'root_id', 4, ['name' => 'Slacks']);
        $nestedSet->insertAsFirstChild('tree', 'root_id',5, ['name' => 'Evening Gowns']);
        $nestedSet->insertAsNextSibling('tree', 'root_id', 10, ['name' => 'Sun Dresses']);
        \NestedSetBootstrap::validateTree(100);

        $this->assertNode(1, 1, 22, 0, 100);
        $this->assertNode(2, 2, 9, 1, 100);
        $this->assertNode(4, 3, 8, 2, 100);
        $this->assertNode(9, 4, 5, 3, 100);
        $this->assertNode(8, 6, 7, 3, 100);
        $this->assertNode(3, 10, 21, 1, 100);
        $this->assertNode(5, 11, 16, 2, 100);
        $this->assertNode(10, 12, 13, 3, 100);
        $this->assertNode(11, 14, 15, 3, 100);
        $this->assertNode(6, 17, 18, 2, 100);
        $this->assertNode(7, 19, 20, 2, 100);
    }

    public function test_insertAsPrevSibling_by_recreating_the_demo_tree_using_insert_as_last_child_where_necessary()
    {
        $nestedSet = $this->nestedSet;

        $nestedSet->insertRoot('tree', 'root_id', 100, ['name' => 'Clothing']);
        $nestedSet->insertAsLastChild('tree', 'root_id', 1, ['name' => 'Women']);
        $nestedSet->insertAsPrevSibling('tree', 'root_id', 2, ['name' => 'Men']);
        $nestedSet->insertAsLastChild('tree', 'root_id', 3, ['name' => 'Suits']);
        $nestedSet->insertAsLastChild('tree', 'root_id', 2, ['name' => 'Blouses']);
        $nestedSet->insertAsPrevSibling('tree', 'root_id', 5, ['name' => 'Dresses']);
        $nestedSet->insertAsPrevSibling('tree', 'root_id', 5, ['name' => 'Skirts']);
        $nestedSet->insertAsLastChild('tree', 'root_id', 4, ['name' => 'Jackets']);
        $nestedSet->insertAsPrevSibling('tree', 'root_id', 8, ['name' => 'Slacks']);
        $nestedSet->insertAsLastChild('tree', 'root_id', 6, ['name' => 'Sun Dresses']);
        $nestedSet->insertAsPrevSibling('tree', 'root_id', 10, ['name' => 'Evening Gowns']);
        \NestedSetBootstrap::validateTree(100);

        $this->assertNode(1, 1, 22, 0, 100);
        $this->assertNode(3, 2, 9, 1, 100);
        $this->assertNode(4, 3, 8, 2, 100);
        $this->assertNode(9, 4, 5, 3, 100);
        $this->assertNode(8, 6, 7, 3, 100);
        $this->assertNode(2, 10, 21, 1, 100);
        $this->assertNode(6, 11, 16, 2, 100);
        $this->assertNode(11, 12, 13, 3, 100);
        $this->assertNode(10, 14, 15, 3, 100);
        $this->assertNode(7, 17, 18, 2, 100);
        $this->assertNode(5, 19, 20, 2, 100);
    }

    public function test_move_as_last_child()
    {
        \NestedSetBootstrap::insertDemoTree();

        $this->nestedSet->moveAsLastChild('tree', 'root_id', 3, 7);

        \NestedSetBootstrap::validateTree(1);

        $this->assertNode(1, 1, 22, 0, 1);
        $this->assertNode(2, 2, 9, 1, 1);
        $this->assertNode(4, 3, 8, 2, 1);
        $this->assertNode(5, 4, 5, 3, 1);
        $this->assertNode(6, 6, 7, 3, 1);
        $this->assertNode(3, 10, 21, 1, 1);
        $this->assertNode(8, 11, 12, 2, 1);
        $this->assertNode(9, 13, 14, 2, 1);
        $this->assertNode(7, 15, 20, 2, 1);
        $this->assertNode(10, 16, 17, 3, 1);
        $this->assertNode(11, 18, 19, 3, 1);
    }

    public function test_move_as_last_child_throws()
    {
        \NestedSetBootstrap::insertDemoTree();

        $this->expectException(InvalidNodeOperationException::class);
        $this->nestedSet->moveAsLastChild('tree', 'root_id', 7, 3);
    }

    public function test_move_as_first_child()
    {
        \NestedSetBootstrap::insertDemoTree();

        $this->nestedSet->moveAsFirstChild('tree', 'root_id', 3, 7);

        \NestedSetBootstrap::validateTree(1);

        $this->assertNode(1, 1, 22, 0, 1);
        $this->assertNode(2, 2, 9, 1, 1);
        $this->assertNode(4, 3, 8, 2, 1);
        $this->assertNode(5, 4, 5, 3, 1);
        $this->assertNode(6, 6, 7, 3, 1);
        $this->assertNode(3, 10, 21, 1, 1);
        $this->assertNode(7, 11, 16, 2, 1);
        $this->assertNode(10, 12, 13, 3, 1);
        $this->assertNode(11, 14, 15, 3, 1);
        $this->assertNode(8, 17, 18, 2, 1);
        $this->assertNode(9, 19, 20, 2, 1);
    }

    public function test_move_as_first_child_throws()
    {
        \NestedSetBootstrap::insertDemoTree();

        $this->expectException(InvalidNodeOperationException::class);
        $this->nestedSet->moveAsFirstChild('tree', 'root_id', 7, 3);
    }

    public function test_move_as_prev_sibling()
    {
        \NestedSetBootstrap::insertDemoTree();

        $this->nestedSet->moveAsPrevSibling('tree', 'root_id', 4, 7);

        \NestedSetBootstrap::validateTree(1);

        $this->assertNode(1, 1, 22, 0, 1);
        $this->assertNode(2, 2, 15, 1, 1);
        $this->assertNode(7, 3, 8, 2, 1);
        $this->assertNode(10, 4, 5, 3, 1);
        $this->assertNode(11, 6, 7, 3, 1);
        $this->assertNode(4, 9, 14, 2, 1);
        $this->assertNode(5, 10, 11, 3, 1);
        $this->assertNode(6, 12, 13, 3, 1);
        $this->assertNode(3, 16, 21, 1, 1);
        $this->assertNode(8, 17, 18, 2, 1);
        $this->assertNode(9, 19, 20, 2, 1);

    }

    public function test_move_as_prev_sibling_throws()
    {
        \NestedSetBootstrap::insertDemoTree();

        $this->expectException(InvalidNodeOperationException::class);
        $this->nestedSet->moveAsPrevSibling('tree', 'root_id', 4, 1);
    }

    public function test_move_as_next_sibling()
    {
        \NestedSetBootstrap::insertDemoTree();

        $this->nestedSet->moveAsNextSibling('tree', 'root_id', 4, 7);

        \NestedSetBootstrap::validateTree(1);

        $this->assertNode(1, 1, 22, 0, 1);
        $this->assertNode(2, 2, 15, 1, 1);
        $this->assertNode(4, 3, 8, 2, 1);
        $this->assertNode(5, 4, 5, 3, 1);
        $this->assertNode(6, 6, 7, 3, 1);
        $this->assertNode(7, 9, 14, 2, 1);
        $this->assertNode(10, 10, 11, 3, 1);
        $this->assertNode(11, 12, 13, 3, 1);
        $this->assertNode(3, 16, 21, 1, 1);
        $this->assertNode(8, 17, 18, 2, 1);
        $this->assertNode(9, 19, 20, 2, 1);
    }

    public function test_move_as_next_sibling_throws()
    {
        \NestedSetBootstrap::insertDemoTree();

        $this->expectException(InvalidNodeOperationException::class);
        $this->nestedSet->moveAsNextSibling('tree', 'root_id', 4, 1);
    }

    public function test_delete()
    {
        \NestedSetBootstrap::insertDemoTree();

        $this->nestedSet->removeNode('tree', 'root_id', 2);

        \NestedSetBootstrap::validateTree(1);

        $this->assertNode(1, 1, 14, 0, 1);
        $this->assertNode(3, 2, 13, 1, 1);
        $this->assertNode(7, 3, 8, 2, 1);
        $this->assertNode(10, 4, 5, 3, 1);
        $this->assertNode(11, 6, 7, 3, 1);
        $this->assertNode(8, 9, 10, 2, 1);
        $this->assertNode(9, 11, 12, 2, 1);
    }

    /**
     * @param int $nodeId
     * @param int $leftValue
     * @param int $rightValue
     * @param int $level
     * @param int $rootId
     */
    private function assertNode(int $nodeId, int $leftValue, int $rightValue, int $level, int $rootId)
    {
        $nodeData = $this->reader->fetchNodeData('tree', 'root_id', $nodeId);

        self::assertSame($leftValue, $nodeData['left'], 'left value');
        self::assertSame($rightValue, $nodeData['right'], 'right value');
        self::assertSame($level, $nodeData['level'], 'level value');
        self::assertSame($rootId, $nodeData['root_id'], 'root id');
    }

    private function printAsserts($rootId = 100)
    {
        $tree = \NestedSetBootstrap::getConnection()->fetchAll('SELECT * FROM tree WHERE root_id = ' . $rootId . ' ORDER BY `left`;');

        echo "\n";
        foreach ($tree as $node) {
            echo '$this->assertNode(' . $node['id'] .  ', ' . $node['left'] . ', ' . $node['right'] . ', ' . $node['level'] . ', 100);' . "\n";
        }
    }
}
