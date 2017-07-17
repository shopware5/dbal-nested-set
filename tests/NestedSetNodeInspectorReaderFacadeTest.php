<?php declare(strict_types=1);


namespace Shopware\DbalNestedSetTest;

use PHPUnit\Framework\TestCase;
use Shopware\DbalNestedSet\NestedSetConventionsConfig;
use Shopware\DbalNestedSet\NestedSetFactory;
use Shopware\DbalNestedSet\NestedSetNodeInspectorReaderFacade;

class NestedSetNodeInspectorReaderFacadeTest extends TestCase
{
    /**
     * @var NestedSetNodeInspectorReaderFacade
     */
    private $nestedSet;

    public function setUp()
    {
        $connection = \NestedSetBootstrap::getConnection();
        $connection->exec(file_get_contents(__DIR__ . '/fixtures.sql'));
        \NestedSetBootstrap::insertDemoTree();
        $this->nestedSet = NestedSetFactory::createReaderNodeInspector($connection, new NestedSetConventionsConfig('id', 'left', 'right', 'level'));
    }

    public function test_is_leaf()
    {
        $this->assertTrue($this->nestedSet->isLeaf('tree', 'root_id', 9));
        $this->assertFalse($this->nestedSet->isLeaf('tree', 'root_id', 2));
    }

    public function test_is_root()
    {
        $this->assertTrue($this->nestedSet->isRoot('tree', 'root_id', 1));
        $this->assertFalse($this->nestedSet->isRoot('tree', 'root_id', 2));
    }

    public function test_is_descandanant()
    {
        $this->assertTrue($this->nestedSet->isDescendant('tree', 'root_id', 2, 1));
        $this->assertFalse($this->nestedSet->isDescendant('tree', 'root_id', 1, 2));
        $this->assertFalse($this->nestedSet->isDescendant('tree', 'root_id', 1, 1));
    }

    public function test_is_descandanant_or_equal()
    {
        $this->assertTrue($this->nestedSet->isDescendantOfOrEqual('tree', 'root_id', 2, 1));
        $this->assertFalse($this->nestedSet->isDescendantOfOrEqual('tree', 'root_id', 1, 2));
        $this->assertTrue($this->nestedSet->isDescendantOfOrEqual('tree', 'root_id', 1, 1));
    }

    public function test_is_ancestor()
    {
        $this->assertTrue($this->nestedSet->isAncestor('tree', 'root_id', 1, 2));
        $this->assertFalse($this->nestedSet->isAncestor('tree', 'root_id', 2, 1));
        $this->assertFalse($this->nestedSet->isAncestor('tree', 'root_id', 1, 1));
    }


}