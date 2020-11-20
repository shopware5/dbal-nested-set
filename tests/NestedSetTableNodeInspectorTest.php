<?php declare(strict_types=1);

namespace Shopware\DbalNestedSetTest;

use NestedSetBootstrap;
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

    public function setUp(): void
    {
        $connection = NestedSetBootstrap::getConnection();
        NestedSetBootstrap::importTable();
        NestedSetBootstrap::insertDemoTree();
        $this->inspector = NestedSetFactory::createTableNodeInspector($connection, new NestedSetConfig('id', 'left', 'right', 'level'));
    }

    public function test_is_leaf(): void
    {
        self::assertTrue($this->inspector->isLeaf('tree', 'root_id', 9));
        self::assertFalse($this->inspector->isLeaf('tree', 'root_id', 2));
    }

    public function test_is_root(): void
    {
        self::assertTrue($this->inspector->isRoot('tree', 'root_id', 1));
        self::assertFalse($this->inspector->isRoot('tree', 'root_id', 2));
    }

    public function test_is_descandanant(): void
    {
        self::assertTrue($this->inspector->isDescendant('tree', 'root_id', 2, 1));
        self::assertFalse($this->inspector->isDescendant('tree', 'root_id', 1, 2));
        self::assertFalse($this->inspector->isDescendant('tree', 'root_id', 1, 1));
    }

    public function test_is_descandanant_or_equal(): void
    {
        self::assertTrue($this->inspector->isDescendantOfOrEqual('tree', 'root_id', 2, 1));
        self::assertFalse($this->inspector->isDescendantOfOrEqual('tree', 'root_id', 1, 2));
        self::assertTrue($this->inspector->isDescendantOfOrEqual('tree', 'root_id', 1, 1));
    }

    public function test_is_ancestor(): void
    {
        self::assertTrue($this->inspector->isAncestor('tree', 'root_id', 1, 2));
        self::assertFalse($this->inspector->isAncestor('tree', 'root_id', 2, 1));
        self::assertFalse($this->inspector->isAncestor('tree', 'root_id', 1, 1));
    }
}
