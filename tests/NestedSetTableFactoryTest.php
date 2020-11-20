<?php declare(strict_types=1);

namespace Shopware\DbalNestedSetTest;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Schema;
use NestedSetBootstrap;
use PHPUnit\Framework\TestCase;
use Shopware\DbalNestedSet\NestedSetConfig;
use Shopware\DbalNestedSet\NestedSetFactory;
use Shopware\DbalNestedSet\NestedSetTableFactory;

class NestedSetTableFactoryTest extends TestCase
{
    /**
     * @var NestedSetTableFactory
     */
    private $factory;

    /**
     * @var AbstractPlatform
     */
    private $platform;

    public function setUp(): void
    {
        $connection = NestedSetBootstrap::getConnection();
        $this->platform = $connection->getDatabasePlatform();
        $this->factory = NestedSetFactory::createTableFactory($connection, new NestedSetConfig('id', 'left', 'right', 'level'));
    }

    public function test_adds_tables_to_the_schema(): void
    {
        $schema = new Schema();
        $this->factory->createTable($schema, 'tree', 'root_id');
        $this->factory->createTable($schema, 'tree2', 'not_root_id');
        $sql = $schema->toSql($this->platform);

        self::assertTrue($schema->getTable('tree2')->hasColumn('not_root_id'));
        self::assertCount(2, $schema->getTables());
        self::assertCount(2, $sql);
    }
}
