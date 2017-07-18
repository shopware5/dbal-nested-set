<?php declare(strict_types=1);

namespace Shopware\DbalNestedSet;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class NestedSetQueryFactory
{
    use NestedSetConventionConfigAware;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var NestedSetReader
     */
    private $reader;

    /**
     * @param Connection $connection
     * @param NestedSetConventionsConfig $conventionsConfig
     */
    public function __construct(Connection $connection, NestedSetReader $reader, NestedSetConventionsConfig $conventionsConfig)
    {
        $this->connection = $connection;
        $this->reader = $reader;
        $this->setUpWithConnection($conventionsConfig, $connection);
    }

    /**
     * @param string $tableExpression
     * @param string $queryAlias
     * @return QueryBuilder
     */
    public function createFetchRootsQueryBuilder(string $tableExpression, string $queryAlias): QueryBuilder
    {
        return $this->connection->createQueryBuilder()
            ->from($this->connection->quoteIdentifier($tableExpression), $queryAlias)
            ->where("{$queryAlias}.{$this->leftCol} = :{$queryAlias}Left")
            ->orderBy("{$queryAlias}.{$this->leftCol}")
            ->setParameter("{$queryAlias}Left", 1)
        ;
    }

    /**
     * @param string $tableExpression
     * @param string $queryAlias
     * @param string $rootColumnName
     * @param int $parentId
     * @return QueryBuilder
     */
    public function createChildrenQueryBuilder(string $tableExpression, string $queryAlias, string $rootColumnName, int $parentId): QueryBuilder
    {
        $this->setRootColName($rootColumnName, $this->connection);
        $nodeData = $this->reader->fetchNodeData($tableExpression, $rootColumnName, $parentId);

        return $this->connection->createQueryBuilder()
            ->from($this->connection->quoteIdentifier($tableExpression), $queryAlias)
            ->where("{$queryAlias}.{$this->levelCol} = :{$queryAlias}Level")
            ->andWhere("{$queryAlias}.{$this->leftCol} > :{$queryAlias}Left")
            ->andWhere("{$queryAlias}.{$this->leftCol} < :{$queryAlias}Right")
            ->andWhere("{$queryAlias}.{$this->rootCol} = :{$queryAlias}Root")
            ->orderBy("{$queryAlias}.{$this->leftCol}")
            ->setParameter("{$queryAlias}Level", 1 + $nodeData['level'])
            ->setParameter("{$queryAlias}Left", $nodeData['left'])
            ->setParameter("{$queryAlias}Right", $nodeData['right'])
            ->setParameter("{$queryAlias}Root", $nodeData['root_id'])
        ;
    }

    /**
     * @param string $tableExpression
     * @param string $queryAlias
     * @param int $parentId
     * @return QueryBuilder
     */
    public function createSubtreeQueryBuilder(string $tableExpression, string $queryAlias, string $rootColumnName, int $parentId): QueryBuilder
    {
        $this->setRootColName($rootColumnName, $this->connection);
        $nodeData = $this->reader->fetchNodeData($tableExpression, $rootColumnName, $parentId);

        return $this->connection->createQueryBuilder()
            ->from($tableExpression, $queryAlias)
            ->where("{$queryAlias}.{$this->leftCol} > :{$queryAlias}Left")
            ->andWhere("{$queryAlias}.{$this->leftCol} < :{$queryAlias}Right")
            ->andWhere("{$queryAlias}.{$this->rootCol} = :{$queryAlias}Root")
            ->orderBy("{$queryAlias}.{$this->leftCol}")
            ->setParameter("{$queryAlias}Left", $nodeData['left'])
            ->setParameter("{$queryAlias}Right", $nodeData['right'])
            ->setParameter("{$queryAlias}Root", $nodeData['root_id'])
        ;
    }

    /**
     * @param string $tableExpression
     * @param string $queryAlias
     * @param int $nodeId
     * @return QueryBuilder
     */
    public function createParentsQueryBuilder(string $tableExpression, string $queryAlias, string $rootColumnName, int $nodeId): QueryBuilder
    {
        $this->setRootColName($rootColumnName, $this->connection);
        $nodeData = $this->reader->fetchNodeData($tableExpression, $rootColumnName, $nodeId);

        return $this->connection->createQueryBuilder()
            ->from($tableExpression, $queryAlias)
            ->where("{$queryAlias}.{$this->leftCol} < :{$queryAlias}Left")
            ->andWhere("{$queryAlias}.{$this->rightCol} > :{$queryAlias}Right")
            ->andWhere("{$queryAlias}.{$this->rootCol} = :{$queryAlias}Root")
            ->orderBy("{$queryAlias}.{$this->leftCol}", 'DESC')
            ->setParameter("{$queryAlias}Left", $nodeData['left'])
            ->setParameter("{$queryAlias}Right", $nodeData['right'])
            ->setParameter("{$queryAlias}Root", $nodeData['root_id'])
        ;
    }
}
