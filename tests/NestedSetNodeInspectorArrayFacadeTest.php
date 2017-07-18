<?php declare(strict_types=1);


namespace Shopware\DbalNestedSetTest;

use PHPUnit\Framework\TestCase;
use Shopware\DbalNestedSet\NestedSetConventionsConfig;
use Shopware\DbalNestedSet\NestedSetFactory;
use Shopware\DbalNestedSet\NestedSetNodeInspectorArrayFacade;
use Shopware\DbalNestedSet\NestedSetReader;

class NestedSetNodeInspectorArrayFacadeTest extends TestCase
{
    /**
     * @var NestedSetNodeInspectorArrayFacade
     */
    private $inspector;

    /**
     * @var NestedSetReader
     */
    private $reader;

    public function setUp()
    {
        $connection = \NestedSetBootstrap::getConnection();
        $connection->exec(file_get_contents(__DIR__ . '/fixtures.sql'));
        \NestedSetBootstrap::insertDemoTree();
        $this->inspector = NestedSetFactory::createArrayNodeInspector();
        $this->reader = NestedSetFactory::createReader($connection, new NestedSetConventionsConfig('id', 'left', 'right', 'level'));
    }

    public function test_is_equal()
    {
        $this->assertTrue($this->inspector->isEqual(
            $this->reader->fetchNodeData('tree', 'root_id', 2),
            $this->reader->fetchNodeData('tree', 'root_id', 2)
        ));
        $this->assertFalse($this->inspector->isEqual(
            $this->reader->fetchNodeData('tree', 'root_id', 1),
            $this->reader->fetchNodeData('tree', 'root_id', 2)
        ));
    }
}
