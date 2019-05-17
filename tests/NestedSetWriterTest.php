<?php declare(strict_types=1);

namespace Shopware\DbalNestedSetTest;

use PHPUnit\Framework\TestCase;
use Shopware\DbalNestedSet\NestedSetConfig;
use Shopware\DbalNestedSet\NestedSetExceptionInvalidNodeOperation;
use Shopware\DbalNestedSet\NestedSetFactory;
use Shopware\DbalNestedSet\NestedSetWriter;
use Shopware\DbalNestedSet\Tool\NestedSetReader;

class NestedSetWriterTest extends TestCase
{
    /**
     * @var NestedSetWriter
     */
    private $writer;

    /**
     * @var NestedSetReader
     */
    private $reader;

    public function setUp()
    {
        $connection = \NestedSetBootstrap::getConnection();
        \NestedSetBootstrap::importTable();
        $this->writer = NestedSetFactory::createWriter($connection, new NestedSetConfig('id', 'left', 'right', 'level'));
        $this->reader = new NestedSetReader($connection, new NestedSetConfig('id', 'left', 'right', 'level'));
    }

    public function test_insertAsLastChild_by_recreating_the_demo_tree()
    {
        $this->writer->insertRoot('tree', 'root_id', 100, ['name' => 'Clothing']);
        $this->writer->insertAsLastChild('tree', 'root_id', 1, ['name' => 'Men']);
        $this->writer->insertAsLastChild('tree', 'root_id', 1, ['name' => 'Women']);
        $this->writer->insertAsLastChild('tree', 'root_id', 2, ['name' => 'Suits']);
        $this->writer->insertAsLastChild('tree', 'root_id', 3, ['name' => 'Dresses']);
        $this->writer->insertAsLastChild('tree', 'root_id', 3, ['name' => 'Skirts']);
        $this->writer->insertAsLastChild('tree', 'root_id', 3, ['name' => 'Blouses']);
        $this->writer->insertAsLastChild('tree', 'root_id', 4, ['name' => 'Slacks']);
        $this->writer->insertAsLastChild('tree', 'root_id', 4, ['name' => 'Jackets']);
        $this->writer->insertAsLastChild('tree', 'root_id', 5, ['name' => 'Evening Gowns']);
        $this->writer->insertAsLastChild('tree', 'root_id', 5, ['name' => 'Sun Dresses']);
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
        $this->writer->insertRoot('tree', 'root_id', 100, ['name' => 'Clothing']);
        $this->writer->insertAsFirstChild('tree', 'root_id', 1, ['name' => 'Women']);
        $this->writer->insertAsFirstChild('tree', 'root_id', 1, ['name' => 'Men']);
        $this->writer->insertAsFirstChild('tree', 'root_id', 3, ['name' => 'Suits']);
        $this->writer->insertAsFirstChild('tree', 'root_id', 2, ['name' => 'Blouses']);
        $this->writer->insertAsFirstChild('tree', 'root_id', 2, ['name' => 'Skirts']);
        $this->writer->insertAsFirstChild('tree', 'root_id', 2, ['name' => 'Dresses']);
        $this->writer->insertAsFirstChild('tree', 'root_id', 4, ['name' => 'Jackets']);
        $this->writer->insertAsFirstChild('tree', 'root_id', 4, ['name' => 'Slacks']);
        $this->writer->insertAsFirstChild('tree', 'root_id', 7, ['name' => 'Sun Dresses']);
        $this->writer->insertAsFirstChild('tree', 'root_id', 7, ['name' => 'Evening Gowns']);
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
        $this->writer->insertRoot('tree', 'root_id', 100, ['name' => 'Clothing']);
        $this->writer->insertAsFirstChild('tree', 'root_id', 1, ['name' => 'Men']);
        $this->writer->insertAsNextSibling('tree', 'root_id', 2, ['name' => 'Women']);
        $this->writer->insertAsFirstChild('tree', 'root_id', 2, ['name' => 'Suits']);
        $this->writer->insertAsFirstChild('tree', 'root_id', 3, ['name' => 'Dresses']);
        $this->writer->insertAsNextSibling('tree', 'root_id', 5, ['name' => 'Skirts']);
        $this->writer->insertAsNextSibling('tree', 'root_id', 6, ['name' => 'Blouses']);
        $this->writer->insertAsFirstChild('tree', 'root_id', 4, ['name' => 'Jackets']);
        $this->writer->insertAsFirstChild('tree', 'root_id', 4, ['name' => 'Slacks']);
        $this->writer->insertAsFirstChild('tree', 'root_id', 5, ['name' => 'Evening Gowns']);
        $this->writer->insertAsNextSibling('tree', 'root_id', 10, ['name' => 'Sun Dresses']);
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
        $this->writer->insertRoot('tree', 'root_id', 100, ['name' => 'Clothing']);
        $this->writer->insertAsLastChild('tree', 'root_id', 1, ['name' => 'Women']);
        $this->writer->insertAsPrevSibling('tree', 'root_id', 2, ['name' => 'Men']);
        $this->writer->insertAsLastChild('tree', 'root_id', 3, ['name' => 'Suits']);
        $this->writer->insertAsLastChild('tree', 'root_id', 2, ['name' => 'Blouses']);
        $this->writer->insertAsPrevSibling('tree', 'root_id', 5, ['name' => 'Dresses']);
        $this->writer->insertAsPrevSibling('tree', 'root_id', 5, ['name' => 'Skirts']);
        $this->writer->insertAsLastChild('tree', 'root_id', 4, ['name' => 'Jackets']);
        $this->writer->insertAsPrevSibling('tree', 'root_id', 8, ['name' => 'Slacks']);
        $this->writer->insertAsLastChild('tree', 'root_id', 6, ['name' => 'Sun Dresses']);
        $this->writer->insertAsPrevSibling('tree', 'root_id', 10, ['name' => 'Evening Gowns']);
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

        $this->writer->moveAsLastChild('tree', 'root_id', 3, 7);

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

    public function test_move_as_last_child_with_different_levels()
    {
        \NestedSetBootstrap::insertDemoTree();

        $this->writer->moveAsLastChild('tree', 'root_id', 3, 2); //make men last child of women

        \NestedSetBootstrap::validateTree(1);

        $this->assertNode(1, 1, 22, 0, 1);
        $this->assertNode(3, 2, 21, 1, 1);
        $this->assertNode(7, 3, 8, 2, 1);
        $this->assertNode(10, 4, 5, 3, 1);
        $this->assertNode(11, 6, 7, 3, 1);
        $this->assertNode(8, 9, 10, 2, 1);
        $this->assertNode(9, 11, 12, 2, 1);
        $this->assertNode(2, 13, 20, 2, 1);
        $this->assertNode(4, 14, 19, 3, 1);
        $this->assertNode(5, 15, 16, 4, 1);
        $this->assertNode(6, 17, 18, 4, 1);
    }

    public function test_move_as_last_child_throws()
    {
        \NestedSetBootstrap::insertDemoTree();

        $this->expectException(NestedSetExceptionInvalidNodeOperation::class);
        $this->writer->moveAsLastChild('tree', 'root_id', 7, 3);
    }

    public function test_move_as_first_child()
    {
        \NestedSetBootstrap::insertDemoTree();

        $this->writer->moveAsFirstChild('tree', 'root_id', 3, 7);

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

        $this->expectException(NestedSetExceptionInvalidNodeOperation::class);
        $this->writer->moveAsFirstChild('tree', 'root_id', 7, 3);
    }

    public function test_move_as_prev_sibling()
    {
        \NestedSetBootstrap::insertDemoTree();

        $this->writer->moveAsPrevSibling('tree', 'root_id', 4, 7);

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

        $this->expectException(NestedSetExceptionInvalidNodeOperation::class);
        $this->writer->moveAsPrevSibling('tree', 'root_id', 4, 1);
    }

    public function test_move_as_next_sibling()
    {
        \NestedSetBootstrap::insertDemoTree();

        $this->writer->moveAsNextSibling('tree', 'root_id', 4, 7);

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

        $this->expectException(NestedSetExceptionInvalidNodeOperation::class);
        $this->writer->moveAsNextSibling('tree', 'root_id', 4, 1);
    }

    public function test_delete()
    {
        \NestedSetBootstrap::insertDemoTree();
        \NestedSetBootstrap::insertDemoTree(2);

        $this->writer->removeNode('tree', 'root_id', 2);

        \NestedSetBootstrap::validateTree(1);

        $this->assertNode(1, 1, 14, 0, 1);
        $this->assertNode(3, 2, 13, 1, 1);
        $this->assertNode(7, 3, 8, 2, 1);
        $this->assertNode(10, 4, 5, 3, 1);
        $this->assertNode(11, 6, 7, 3, 1);
        $this->assertNode(8, 9, 10, 2, 1);
        $this->assertNode(9, 11, 12, 2, 1);

        // important check if removeNode didn't delete entries from root_id 2
        $this->assertNode(22, 2, 9, 1, 2);
    }

    private function assertNode(int $nodeId, int $leftValue, int $rightValue, int $level, int $rootId)
    {
        $nodeData = $this->reader->fetchNodeData('tree', 'root_id', $nodeId);

        self::assertSame($leftValue, $nodeData['left'], 'left value');
        self::assertSame($rightValue, $nodeData['right'], 'right value');
        self::assertSame($level, $nodeData['level'], 'level value');
        self::assertSame($rootId, $nodeData['root_id'], 'root id');
    }
}
