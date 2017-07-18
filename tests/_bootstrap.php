<?php declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;

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
        $connectionParams = array(
            'dbname' => 'nested_set',
            'user' => 'root',
            'password' => 'root',
            'host' => 'localhost',
            'driver' => 'pdo_mysql',
        );

        return self::$connection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
    }

    public static function validateTree(int $rootId)
    {
        $tree = self::getConnection()->fetchAll('SELECT * FROM tree WHERE root_id = :rootId ORDER BY `left`;', ['rootId' => $rootId]);

        foreach ($tree as $node) {
            $leftEven = (($node['left'] % 2) === 0);
            $rightEven = (($node['right'] % 2) === 0);
            \PHPUnit\Framework\TestCase::assertNotEquals($leftEven, $rightEven, "\nERROR {$node['name']} is invalid\n");
        }
    }

    public static function printTree(int $rootId)
    {
        $tree = self::getConnection()->fetchAll('SELECT * FROM tree WHERE root_id = :rootId ORDER BY `left`;', ['rootId' => $rootId]);

        echo "\n";
        echo "id\tLeft\tRight\tLevel\tName\n";
        foreach ($tree as $node) {
            echo $node['id'] . "\t" . $node['left'] . "\t " . $node['right'] . "\t " . $node['level'] . "\t";

            for ($i = 0; $i < $node['level']; $i++) {
                echo "\t";
            }

            echo ' ' . $node['name'] . "\n";
        }
    }

    public static function insertDemoTree(int $rootId = 1)
    {
        self::getConnection()->exec('
            INSERT INTO tree (`id`, `left`, `right`, `level`, `root_id`, `name`) VALUES
               ( 1,  1, 22, 0, ' . $rootId . ', \'Clothing\')
              ,( 2,  2,  9, 1, ' . $rootId . ', \'Mens\')
              ,( 4,  3,  8, 2, ' . $rootId . ', \'Suits\')
              ,( 5,  4,  5, 3, ' . $rootId . ', \'Slacks\')
              ,( 6,  6,  7, 3, ' . $rootId . ', \'Jackets\')
              ,( 3, 10, 21, 1, ' . $rootId . ', \'Women\')
              ,( 7, 11, 16, 2, ' . $rootId . ', \'Dresses\')
              ,(10, 12, 13, 3, ' . $rootId . ', \'Evening Growns\')
              ,(11, 14, 15, 3, ' . $rootId . ', \'Sun Dresses\')
              ,( 8, 17, 18, 2, ' . $rootId . ', \'Skirts\')
              ,( 9, 19, 20, 2, ' . $rootId . ', \'Blouses\')
            ;
        ');
    }
}
