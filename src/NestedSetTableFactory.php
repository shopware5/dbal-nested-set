<?php declare(strict_types=1);

namespace Shopware\DbalNestedSet;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Shopware\DbalNestedSet\Tool\NestedSetConfigAware;

class NestedSetTableFactory
{
    use NestedSetConfigAware;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection, NestedSetConfig $config)
    {
        $this->connection = $connection;
        $this->setUpWithConnection($config, $connection);
    }

    public function createTable(Schema $schema, string $tableName, string $rootColumnName): Table
    {
        $this->setRootColName($rootColumnName, $this->connection);

        $treeTable = $schema->createTable($this->connection->quoteIdentifier($tableName));

        $treeTable->addColumn($this->leftCol, 'integer', ['unsigned' => true]);
        $treeTable->addColumn($this->rightCol, 'integer', ['unsigned' => true]);
        $treeTable->addColumn($this->levelCol, 'integer', ['unsigned' => true]);
        $treeTable->addColumn($this->rootCol, 'integer', ['unsigned' => true]);

        $treeTable->addIndex([$this->leftCol])
            ->addIndex([$this->rightCol])
            ->addIndex([$this->levelCol])
            ->addIndex([$this->rootCol]);

        return $treeTable;
    }
}
