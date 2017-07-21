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

    /**
     * @param Connection $connection
     * @param NestedSetConfig $config
     */
    public function __construct(Connection $connection, NestedSetConfig $config)
    {
        $this->connection = $connection;
        $this->setUpWithConnection($config, $connection);
    }

    /**
     * @param Schema $schema
     * @param string $tableName
     * @param string $rootColumnName
     * @return Table
     */
    public function createTable(Schema $schema, string $tableName, string $rootColumnName): Table
    {
        $this->setRootColName($rootColumnName, $this->connection);

        $treeTable = $schema->createTable($this->connection->quoteIdentifier($tableName));

        $treeTable->addColumn($this->leftCol, 'integer', array('unsigned' => true));
        $treeTable->addColumn($this->rightCol, 'integer', array('unsigned' => true));
        $treeTable->addColumn($this->levelCol, 'integer', array('unsigned' => true));
        $treeTable->addColumn($this->rootCol, 'integer', array('unsigned' => true));

        $treeTable->addIndex(array($this->leftCol))
            ->addIndex(array($this->rightCol))
            ->addIndex(array($this->levelCol))
            ->addIndex(array($this->rootCol));

        return $treeTable;
    }
}
