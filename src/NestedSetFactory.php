<?php declare(strict_types=1);

namespace Shopware\DbalNestedSet;

use Doctrine\DBAL\Connection;

class NestedSetFactory
{
    public static function createWriter(Connection $connection, NestedSetConventionsConfig $config): NestedSetWriter
    {
        return new NestedSetWriter($connection, self::createReader($connection, $config), self::createArrayNodeInspector(), $config);
    }

    public static function createQueryFactory(Connection $connection, NestedSetConventionsConfig $config): NestedSetQueryFactory
    {
        return new NestedSetQueryFactory($connection, self::createReader($connection, $config), $config);
    }

    public static function createReader(Connection $connection, NestedSetConventionsConfig $config): NestedSetReader
    {
        return new NestedSetReader($connection, $config);
    }

    public static function createNodeInspector(): NestedSetNodeInspector
    {
        return new NestedSetNodeInspector();
    }

    public static function createArrayNodeInspector(): NestedSetNodeInspectorArrayFacade
    {
        return new NestedSetNodeInspectorArrayFacade(self::createNodeInspector());
    }

    public static function createReaderNodeInspector(Connection $connection, NestedSetConventionsConfig $config): NestedSetNodeInspectorReaderFacade
    {
        return new NestedSetNodeInspectorReaderFacade(self::createArrayNodeInspector(), self::createReader($connection, $config));
    }
}