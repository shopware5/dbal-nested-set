<?php

declare(strict_types=1);

namespace Shopware\DbalNestedSetTest;

use PHPUnit\Framework\TestCase;
use Shopware\DbalNestedSet\NestedSetConfig;
use Shopware\DbalNestedSet\NestedSetFactory;
use Shopware\DbalNestedSet\NestedSetTableNodeInspector;

class NestedSetTableNodeInspectorTest extends TestCase
{
    /**
     * @var NestedSetTableNodeInspector
     */
    private $inspector;

    protected function setUp(): void
    {
        $connection = \NestedSetBootstrap::getConnection();
        \NestedSetBootstrap::importTable();
        \NestedSetBootstrap::insertDemoTree();
        $this->inspector = NestedSetFactory::createTableNodeInspector($connection, new NestedSetConfig('id', 'left', 'right', 'level'));
    }

    public function testIsLeaf(): void
    {
        static::assertTrue($this->inspector->isLeaf('tree', 'root_id', 9));
        static::assertFalse($this->inspector->isLeaf('tree', 'root_id', 2));
    }

    public function testIsRoot(): void
    {
        static::assertTrue($this->inspector->isRoot('tree', 'root_id', 1));
        static::assertFalse($this->inspector->isRoot('tree', 'root_id', 2));
    }

    public function testIsDescandanant(): void
    {
        static::assertTrue($this->inspector->isDescendant('tree', 'root_id', 2, 1));
        static::assertFalse($this->inspector->isDescendant('tree', 'root_id', 1, 2));
        static::assertFalse($this->inspector->isDescendant('tree', 'root_id', 1, 1));
    }

    public function testIsDescandanantOrEqual(): void
    {
        static::assertTrue($this->inspector->isDescendantOfOrEqual('tree', 'root_id', 2, 1));
        static::assertFalse($this->inspector->isDescendantOfOrEqual('tree', 'root_id', 1, 2));
        static::assertTrue($this->inspector->isDescendantOfOrEqual('tree', 'root_id', 1, 1));
    }

    public function testIsAncestor(): void
    {
        static::assertTrue($this->inspector->isAncestor('tree', 'root_id', 1, 2));
        static::assertFalse($this->inspector->isAncestor('tree', 'root_id', 2, 1));
        static::assertFalse($this->inspector->isAncestor('tree', 'root_id', 1, 1));
    }
}
