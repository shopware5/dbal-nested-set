<?php

declare(strict_types=1);

namespace Shopware\DbalNestedSetTest\Tool;

use PHPUnit\Framework\TestCase;
use Shopware\DbalNestedSet\NestedSetConfig;
use Shopware\DbalNestedSet\Tool\NestedSetArrayNodeInspector;
use Shopware\DbalNestedSet\Tool\NestedSetNodeInspector;
use Shopware\DbalNestedSet\Tool\NestedSetReader;

class NestedSetArrayNodeInspectorTest extends TestCase
{
    /**
     * @var NestedSetArrayNodeInspector
     */
    private $inspector;

    /**
     * @var NestedSetReader
     */
    private $reader;

    protected function setUp(): void
    {
        $connection = \NestedSetBootstrap::getConnection();
        \NestedSetBootstrap::importTable();
        \NestedSetBootstrap::insertDemoTree();
        $this->inspector = new NestedSetArrayNodeInspector(new NestedSetNodeInspector());
        $this->reader = new NestedSetReader($connection, new NestedSetConfig('id', 'left', 'right', 'level'));
    }

    public function testIsEqual(): void
    {
        static::assertTrue($this->inspector->isEqual(
            $this->reader->fetchNodeData('tree', 'root_id', 2),
            $this->reader->fetchNodeData('tree', 'root_id', 2)
        ));
        static::assertFalse($this->inspector->isEqual(
            $this->reader->fetchNodeData('tree', 'root_id', 1),
            $this->reader->fetchNodeData('tree', 'root_id', 2)
        ));
    }
}
