<?php declare(strict_types=1);

namespace Shopware\DbalNestedSetTest;

use PHPUnit\Framework\TestCase;
use Shopware\DbalNestedSet\NestedSetConventionsConfig;
use Shopware\DbalNestedSet\NestedSetFactory;
use Shopware\DbalNestedSet\NestedSetReader;
use Shopware\DbalNestedSet\NodeNotFoundException;

class NestedSetReaderTest extends TestCase
{
    /**
     * @var NestedSetReader
     */
    private $nestedSet;

    public function setUp()
    {
        $connection = \NestedSetBootstrap::getConnection();
        $connection->exec(file_get_contents(__DIR__ . '/fixtures.sql'));
        \NestedSetBootstrap::insertDemoTree();
        $this->nestedSet = NestedSetFactory::createReader($connection, new NestedSetConventionsConfig('id', 'left', 'right', 'level'));
    }

    public function test_fetch_a_node()
    {
        $node = $this->nestedSet
            ->fetchNodeData('tree', 'root_id', 2);

        $this->assertEquals(2, $node['id']);
        $this->assertEquals(2, $node['left']);
        $this->assertEquals(9, $node['right']);
        $this->assertEquals(1, $node['level']);
        $this->assertEquals(1, $node['root_id']);
    }

    public function test_fetch_a_node_not_found()
    {
        $this->expectException(NodeNotFoundException::class);
        $this->nestedSet
            ->fetchNodeData('tree', 'root_id', 123465789);
    }
}
