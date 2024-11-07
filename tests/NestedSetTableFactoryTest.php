<?php

declare(strict_types=1);

namespace Shopware\DbalNestedSetTest;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Schema;
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

    protected function setUp(): void
    {
        $connection = \NestedSetBootstrap::getConnection();
        $this->platform = $connection->getDatabasePlatform();
        $this->factory = NestedSetFactory::createTableFactory($connection, new NestedSetConfig('id', 'left', 'right', 'level'));
    }

    public function testAddsTablesToTheSchema(): void
    {
        $schema = new Schema();
        $this->factory->createTable($schema, 'tree', 'root_id');
        $this->factory->createTable($schema, 'tree2', 'not_root_id');
        $sql = $schema->toSql($this->platform);

        static::assertTrue($schema->getTable('tree2')->hasColumn('not_root_id'));
        static::assertCount(2, $schema->getTables());
        static::assertCount(2, $sql);
    }
}
