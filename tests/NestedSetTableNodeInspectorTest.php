<?php declare(strict_types=1);

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

    public function setUp()
    {
        $connection = \NestedSetBootstrap::getConnection();
        \NestedSetBootstrap::importTable();
        \NestedSetBootstrap::insertDemoTree();
        $this->inspector = NestedSetFactory::createTableNodeInspector($connection, new NestedSetConfig('id', 'left', 'right', 'level'));
    }

    public function test_is_leaf()
    {
        $this->assertTrue($this->inspector->isLeaf('tree', 'root_id', 9));
        $this->assertFalse($this->inspector->isLeaf('tree', 'root_id', 2));
    }

    public function test_is_root()
    {
        $this->assertTrue($this->inspector->isRoot('tree', 'root_id', 1));
        $this->assertFalse($this->inspector->isRoot('tree', 'root_id', 2));
    }

    public function test_is_descandanant()
    {
        $this->assertTrue($this->inspector->isDescendant('tree', 'root_id', 2, 1));
        $this->assertFalse($this->inspector->isDescendant('tree', 'root_id', 1, 2));
        $this->assertFalse($this->inspector->isDescendant('tree', 'root_id', 1, 1));
    }

    public function test_is_descandanant_or_equal()
    {
        $this->assertTrue($this->inspector->isDescendantOfOrEqual('tree', 'root_id', 2, 1));
        $this->assertFalse($this->inspector->isDescendantOfOrEqual('tree', 'root_id', 1, 2));
        $this->assertTrue($this->inspector->isDescendantOfOrEqual('tree', 'root_id', 1, 1));
    }

    public function test_is_ancestor()
    {
        $this->assertTrue($this->inspector->isAncestor('tree', 'root_id', 1, 2));
        $this->assertFalse($this->inspector->isAncestor('tree', 'root_id', 2, 1));
        $this->assertFalse($this->inspector->isAncestor('tree', 'root_id', 1, 1));
    }
}
