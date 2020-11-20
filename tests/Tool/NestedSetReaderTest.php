<?php declare(strict_types=1);

namespace Shopware\DbalNestedSetTest;

use NestedSetBootstrap;
use PHPUnit\Framework\TestCase;
use Shopware\DbalNestedSet\NestedSetConfig;
use Shopware\DbalNestedSet\NestedSetExceptionNodeNotFound;
use Shopware\DbalNestedSet\Tool\NestedSetReader;

class NestedSetReaderTest extends TestCase
{
    /**
     * @var NestedSetReader
     */
    private $reader;

    public function setUp(): void
    {
        $connection = NestedSetBootstrap::getConnection();
        NestedSetBootstrap::importTable();
        NestedSetBootstrap::insertDemoTree();
        $this->reader = new NestedSetReader($connection, new NestedSetConfig('id', 'left', 'right', 'level'));
    }

    public function test_fetch_a_node(): void
    {
        $node = $this->reader
            ->fetchNodeData('tree', 'root_id', 2);

        self::assertEquals(2, $node['id']);
        self::assertEquals(2, $node['left']);
        self::assertEquals(9, $node['right']);
        self::assertEquals(1, $node['level']);
        self::assertEquals(1, $node['root_id']);
    }

    public function test_fetch_a_node_not_found(): void
    {
        $this->expectException(NestedSetExceptionNodeNotFound::class);
        $this->reader
            ->fetchNodeData('tree', 'root_id', 123465789);
    }
}
