<?php

declare(strict_types=1);

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

    protected function setUp(): void
    {
        $connection = \NestedSetBootstrap::getConnection();
        \NestedSetBootstrap::importTable();
        \NestedSetBootstrap::insertDemoTree();
        $this->reader = new NestedSetReader($connection, new NestedSetConfig('id', 'left', 'right', 'level'));
    }

    public function testFetchANode(): void
    {
        $node = $this->reader
            ->fetchNodeData('tree', 'root_id', 2);

        static::assertEquals(2, $node['id']);
        static::assertEquals(2, $node['left']);
        static::assertEquals(9, $node['right']);
        static::assertEquals(1, $node['level']);
        static::assertEquals(1, $node['root_id']);
    }

    public function testFetchANodeNotFound(): void
    {
        $this->expectException(NestedSetExceptionNodeNotFound::class);
        $this->reader
            ->fetchNodeData('tree', 'root_id', 123465789);
    }
}
