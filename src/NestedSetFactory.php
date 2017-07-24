<?php declare(strict_types=1);

namespace Shopware\DbalNestedSet;

use Doctrine\DBAL\Connection;
use Shopware\DbalNestedSet\Tool\NestedSetArrayNodeInspector;
use Shopware\DbalNestedSet\Tool\NestedSetNodeInspector;
use Shopware\DbalNestedSet\Tool\NestedSetReader;

class NestedSetFactory
{
    /**
     * @param Connection $connection
     * @param NestedSetConfig $config
     * @return NestedSetTableFactory
     */
    public static function createTableFactory(Connection $connection, NestedSetConfig $config): NestedSetTableFactory
    {
        return new NestedSetTableFactory($connection, $config);
    }

    /**
     * @param Connection $connection
     * @param NestedSetConfig $config
     * @return NestedSetWriter
     */
    public static function createWriter(Connection $connection, NestedSetConfig $config): NestedSetWriter
    {
        return new NestedSetWriter($connection, self::createReader($connection, $config), self::createArrayNodeInspector(), $config);
    }

    /**
     * @param Connection $connection
     * @param NestedSetConfig $config
     * @return NestedSetQueryFactory
     */
    public static function createQueryFactory(Connection $connection, NestedSetConfig $config): NestedSetQueryFactory
    {
        return new NestedSetQueryFactory($connection, self::createReader($connection, $config), $config);
    }

    /**
     * @param Connection $connection
     * @param NestedSetConfig $config
     * @return NestedSetTableNodeInspector
     */
    public static function createTableNodeInspector(Connection $connection, NestedSetConfig $config): NestedSetTableNodeInspector
    {
        return new NestedSetTableNodeInspector(self::createArrayNodeInspector(), self::createReader($connection, $config));
    }

    /**
     * @param Connection $connection
     * @param NestedSetConfig $config
     * @return NestedSetReader
     */
    private static function createReader(Connection $connection, NestedSetConfig $config): NestedSetReader
    {
        return new NestedSetReader($connection, $config);
    }

    /**
     * @return NestedSetNodeInspector
     */
    private static function createNodeInspector(): NestedSetNodeInspector
    {
        return new NestedSetNodeInspector();
    }

    /**
     * @return NestedSetArrayNodeInspector
     */
    private static function createArrayNodeInspector(): NestedSetArrayNodeInspector
    {
        return new NestedSetArrayNodeInspector(self::createNodeInspector());
    }
}
