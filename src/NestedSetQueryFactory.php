<?php declare(strict_types=1);

namespace Shopware\DbalNestedSet;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\DbalNestedSet\Tool\NestedSetConfigAware;
use Shopware\DbalNestedSet\Tool\NestedSetReader;

class NestedSetQueryFactory
{
    use NestedSetConfigAware;

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
     * @param NestedSetConfig $conventionsConfig
     * @param NestedSetReader $reader
     */
    public function __construct(Connection $connection, NestedSetReader $reader, NestedSetConfig $conventionsConfig)
    {
        $this->connection = $connection;
        $this->reader = $reader;
        $this->setUpWithConnection($conventionsConfig, $connection);
    }

    /**
     * Get all roots from a multi root nested set table
     *
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
            ->setParameter("{$queryAlias}Left", 1);
    }

    /**
     * Get all direct children of a particular node
     *
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
            ->setParameter("{$queryAlias}Root", $nodeData['root_id']);
    }

    /**
     * Get the subtree relative to a single node
     *
     * @param string $tableExpression
     * @param string $queryAlias
     * @param string $rootColumnName
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
            ->setParameter("{$queryAlias}Root", $nodeData['root_id']);
    }

    /**
     * Get the parents of a particular node
     *
     * @param string $tableExpression
     * @param string $queryAlias
     * @param string $rootColumnName
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
            ->setParameter("{$queryAlias}Root", $nodeData['root_id']);
    }

    /**
     * Get the whole subtree relative to a collection of nodes ids
     *
     * @param string $tableExpression
     * @param string $queryAlias
     * @param string $rootColumnName
     * @param int[] $nodeIds
     * @param int $minChildLevel
     * @param int $pkType
     * @return QueryBuilder
     */
    public function createSubtreeThroughMultipleNodesQueryBuilder(string $tableExpression, string $queryAlias, string $rootColumnName, array $nodeIds, int $minChildLevel = 1, int $pkType = Connection::PARAM_INT_ARRAY): QueryBuilder
    {
        $this->setRootColName($rootColumnName, $this->connection);

        $directNodeSubSelect = $this->connection->createQueryBuilder()
            ->select([
                "{$queryAlias}directNode." . $this->leftCol,
                "{$queryAlias}directNode." . $this->rightCol,
                "{$queryAlias}directNode." . $this->levelCol,
                "{$queryAlias}directNode." . $this->rootCol,
            ])
            ->from($this->connection->quoteIdentifier($tableExpression), "{$queryAlias}directNode")
            ->andWhere("{$queryAlias}directNode.{$this->pkCol} IN (:{$queryAlias}nodeIds)");

        $siblingQuery = $this->connection->createQueryBuilder()
            ->select([
                "{$queryAlias}SiblingNode." . $this->leftCol,
                "{$queryAlias}SiblingNode." . $this->rightCol,
                "{$queryAlias}SiblingNode." . $this->levelCol,
                "{$queryAlias}SiblingNode." . $this->rootCol,
            ])
            ->from($this->connection->quoteIdentifier($tableExpression), "{$queryAlias}SiblingNode")
            ->innerJoin(
                "{$queryAlias}SiblingNode",
                '(' . $directNodeSubSelect->getSQL() . ')',
                "{$queryAlias}SelectedNode",
                "
                    {$queryAlias}SiblingNode.{$this->leftCol} >= {$queryAlias}SelectedNode.{$this->leftCol} 
                AND {$queryAlias}SiblingNode.{$this->rightCol} >= {$queryAlias}SelectedNode.{$this->rightCol} 
                AND {$queryAlias}SiblingNode.{$this->levelCol} = {$queryAlias}SelectedNode.{$this->levelCol}
                AND {$queryAlias}SiblingNode.{$this->rootCol} = {$queryAlias}SelectedNode.{$this->rootCol}
                "
            );

        $childrenQuery = $this->connection->createQueryBuilder()
            ->select([
                "{$queryAlias}ChildNode." . $this->leftCol,
                "{$queryAlias}ChildNode." . $this->rightCol,
                "{$queryAlias}ChildNode." . $this->levelCol,
                "{$queryAlias}ChildNode." . $this->rootCol,
            ])
            ->from($this->connection->quoteIdentifier($tableExpression), "{$queryAlias}ChildNode")
            ->innerJoin(
                "{$queryAlias}ChildNode",
                '(' . $directNodeSubSelect->getSQL() . ')',
                "{$queryAlias}SelectedNode",
                "
                    {$queryAlias}ChildNode.{$this->leftCol} > {$queryAlias}SelectedNode.{$this->leftCol} 
                AND {$queryAlias}ChildNode.{$this->rightCol} < {$queryAlias}SelectedNode.{$this->rightCol} 
                AND {$queryAlias}ChildNode.{$this->levelCol} <= ({$queryAlias}SelectedNode.{$this->levelCol} + :{$queryAlias}maxChildLevel)
                AND {$queryAlias}ChildNode.{$this->rootCol} = {$queryAlias}SelectedNode.{$this->rootCol}
                "
            );

        $idQuery = $this->connection->createQueryBuilder()
            ->select("{$queryAlias}Group.{$this->pkCol}")
            ->from($this->connection->quoteIdentifier($tableExpression), "{$queryAlias}Group")
            ->innerJoin(
                "{$queryAlias}Group",
                '((' . $childrenQuery->getSQL() . ') UNION (' . $siblingQuery->getSQL() . '))',
                "{$queryAlias}SourceNode",
                "
                    {$queryAlias}Group.{$this->leftCol} <= {$queryAlias}SourceNode.{$this->leftCol} 
                AND {$queryAlias}Group.{$this->rightCol} >= {$queryAlias}SourceNode.{$this->rightCol}
                AND {$queryAlias}Group.{$this->rootCol} = {$queryAlias}SourceNode.{$this->rootCol}
                "
            )
            ->groupBy("{$queryAlias}Group.id");

        return $this->connection->createQueryBuilder()
            ->from($this->connection->quoteIdentifier($tableExpression), $queryAlias)
            ->innerJoin(
                $queryAlias,
                '(' . $idQuery->getSQL() . ')',
                "{$queryAlias}NodeId",
                "{$queryAlias}.{$this->pkCol} = {$queryAlias}NodeId.{$this->pkCol}"
            )
            ->orderBy("{$queryAlias}.{$this->leftCol}")
            ->setParameter("{$queryAlias}nodeIds", $nodeIds, $pkType)
            ->setParameter("{$queryAlias}maxChildLevel", $minChildLevel);
    }
}
