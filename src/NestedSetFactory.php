<?php declare(strict_types=1);

namespace Shopware\DbalNestedSet;

use Doctrine\DBAL\Connection;

class NestedSetFactory
{
    /**
     * @param Connection $connection
     * @param NestedSetConventionsConfig $config
     * @return NestedSetWriter
     */
    public static function createWriter(Connection $connection, NestedSetConventionsConfig $config): NestedSetWriter
    {
        return new NestedSetWriter($connection, self::createReader($connection, $config), self::createArrayNodeInspector(), $config);
    }

    /**
     * @param Connection $connection
     * @param NestedSetConventionsConfig $config
     * @return NestedSetQueryFactory
     */
    public static function createQueryFactory(Connection $connection, NestedSetConventionsConfig $config): NestedSetQueryFactory
    {
        return new NestedSetQueryFactory($connection, self::createReader($connection, $config), $config);
    }

    /**
     * @param Connection $connection
     * @param NestedSetConventionsConfig $config
     * @return NestedSetReader
     */
    public static function createReader(Connection $connection, NestedSetConventionsConfig $config): NestedSetReader
    {
        return new NestedSetReader($connection, $config);
    }

    /**
     * @return NestedSetNodeInspector
     */
    public static function createNodeInspector(): NestedSetNodeInspector
    {
        return new NestedSetNodeInspector();
    }

    /**
     * @return NestedSetNodeInspectorArrayFacade
     */
    public static function createArrayNodeInspector(): NestedSetNodeInspectorArrayFacade
    {
        return new NestedSetNodeInspectorArrayFacade(self::createNodeInspector());
    }

    /**
     * @param Connection $connection
     * @param NestedSetConventionsConfig $config
     * @return NestedSetNodeInspectorReaderFacade
     */
    public static function createReaderNodeInspector(Connection $connection, NestedSetConventionsConfig $config): NestedSetNodeInspectorReaderFacade
    {
        return new NestedSetNodeInspectorReaderFacade(self::createArrayNodeInspector(), self::createReader($connection, $config));
    }
}