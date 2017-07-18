<?php declare(strict_types=1);

namespace Shopware\DbalNestedSetTest;

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

    public function setUp()
    {
        $connection = \NestedSetBootstrap::getConnection();
        $connection->exec(file_get_contents(__DIR__ . '/../_fixtures.sql'));
        \NestedSetBootstrap::insertDemoTree();
        $this->reader = new NestedSetReader($connection, new NestedSetConfig('id', 'left', 'right', 'level'));
    }

    public function test_fetch_a_node()
    {
        $node = $this->reader
            ->fetchNodeData('tree', 'root_id', 2);

        $this->assertEquals(2, $node['id']);
        $this->assertEquals(2, $node['left']);
        $this->assertEquals(9, $node['right']);
        $this->assertEquals(1, $node['level']);
        $this->assertEquals(1, $node['root_id']);
    }

    public function test_fetch_a_node_not_found()
    {
        $this->expectException(NestedSetExceptionNodeNotFound::class);
        $this->reader
            ->fetchNodeData('tree', 'root_id', 123465789);
    }
}
