<?php declare(strict_types=1);

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

    public function setUp()
    {
        $connection = \NestedSetBootstrap::getConnection();
        $connection->exec(file_get_contents(__DIR__ . '/../_fixtures.sql'));
        \NestedSetBootstrap::insertDemoTree();
        $this->inspector = new NestedSetArrayNodeInspector(new NestedSetNodeInspector());
        $this->reader = new NestedSetReader($connection, new NestedSetConfig('id', 'left', 'right', 'level'));
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
