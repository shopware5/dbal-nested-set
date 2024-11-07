<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\TestCase;

class NestedSetBootstrap
{
    /**
     * @var Connection
     */
    public static $connection;

    public static function getConnection(): Connection
    {
        if (self::$connection) {
            return self::$connection;
        }

        $config = new Configuration();
        $connectionParams = [
            'dbname' => DB_NAME,
            'user' => DB_USER,
            'password' => DB_PASSWORD,
            'host' => DB_HOST,
            'driver' => 'pdo_mysql',
        ];

        return self::$connection = DriverManager::getConnection($connectionParams, $config);
    }

    public static function validateTree(int $rootId): void
    {
        $tree = self::getConnection()->fetchAll(
            'SELECT * FROM tree WHERE root_id = :rootId ORDER BY `left`;',
            ['rootId' => $rootId]
        );

        foreach ($tree as $node) {
            $leftEven = (($node['left'] % 2) === 0);
            $rightEven = (($node['right'] % 2) === 0);
            TestCase::assertNotEquals($leftEven, $rightEven, "\nERROR {$node['name']} is invalid\n");
        }
    }

    public static function printTree(int $rootId): void
    {
        $tree = self::getConnection()->fetchAll('SELECT * FROM tree WHERE root_id = :rootId ORDER BY `left`;', ['rootId' => $rootId]);

        echo "\n";
        echo "id\tLeft\tRight\tLevel\tName\n";
        foreach ($tree as $node) {
            echo $node['id'] . "\t" . $node['left'] . "\t " . $node['right'] . "\t " . $node['level'] . "\t";

            for ($i = 0; $i < $node['level']; ++$i) {
                echo "\t";
            }

            echo ' ' . $node['name'] . "\n";
        }
    }

    public static function importTable(): void
    {
        $tableFactory = Shopware\DbalNestedSet\NestedSetFactory::createTableFactory(
            self::getConnection(),
            new Shopware\DbalNestedSet\NestedSetConfig('id', 'left', 'right', 'level')
        );

        $schema = new Doctrine\DBAL\Schema\Schema();
        $table = $tableFactory->createTable($schema, 'tree', 'root_id');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);

        $dropSql = $schema->toDropSql(self::getConnection()->getDatabasePlatform());
        $addSql = $schema->toSql(self::getConnection()->getDatabasePlatform());

        try {
            self::getConnection()->exec($dropSql[0]);
        } catch (Doctrine\DBAL\Exception\TableNotFoundException $e) {
            // nth
        }
        self::getConnection()->exec($addSql[0]);
    }

    public static function insertDemoTree(int $rootId = 1): void
    {
        $data = [
            [1,  1, 22, 0, 'Clothing'],
            [2,  2,  9, 1, 'Mens'],
            [4,  3,  8, 2, 'Suits'],
            [5,  4,  5, 3, 'Slacks'],
            [6,  6,  7, 3, 'Jackets'],
            [3, 10, 21, 1, 'Women'],
            [7, 11, 16, 2, 'Dresses'],
            [10, 12, 13, 3, 'Evening Growns'],
            [11, 14, 15, 3, 'Sun Dresses'],
            [8, 17, 18, 2, 'Skirts'],
            [9, 19, 20, 2, 'Blouses'],
        ];

        foreach ($data as list($id, $left, $right, $level, $name)) {
            self::getConnection()->insert('tree', [
                '`id`' => $id + (20 * ($rootId - 1)),
                '`left`' => $left,
                '`right`' => $right,
                '`level`' => $level,
                '`root_id`' => $rootId,
                '`name`' => $name,
            ]);
        }
    }

    public static function getEnvOrSet(string $name, string $defaultValue): void
    {
        $value = getenv($name);

        if ($value === false) {
            $value = $defaultValue;
        }

        define($name, $value);
    }
}

NestedSetBootstrap::getEnvOrSet('DB_PASSWORD', 'root');
NestedSetBootstrap::getEnvOrSet('DB_USER', 'root');
NestedSetBootstrap::getEnvOrSet('DB_HOST', 'localhost');
NestedSetBootstrap::getEnvOrSet('DB_NAME', 'nested_set');
