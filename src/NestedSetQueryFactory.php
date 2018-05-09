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
    public function createChildrenQueryBuilder(
        string $tableExpression,
        string $queryAlias,
        string $rootColumnName,
        int $parentId
    ): QueryBuilder {
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
     * Get a particular node and all his children
     *
     * @param string $tableExpression
     * @param string $queryAlias
     * @param string $rootColumnName
     * @param int $parentId
     * @param int $nodeId
     * @return QueryBuilder
     */
    public function createNodeAndChildrenQueryBuilder(
        string $tableExpression,
        string $queryAlias,
        string $rootColumnName,
        int $nodeId
    ): QueryBuilder {
        $this->setRootColName($rootColumnName, $this->connection);
        $nodeData = $this->reader->fetchNodeData($tableExpression, $rootColumnName, $nodeId);

        return $this->connection->createQueryBuilder()
            ->from($this->connection->quoteIdentifier($tableExpression), $queryAlias)
            ->where("{$queryAlias}.{$this->levelCol} >= :{$queryAlias}Level")
            ->andWhere("{$queryAlias}.{$this->leftCol} >= :{$queryAlias}Left")
            ->andWhere("{$queryAlias}.{$this->leftCol} <= :{$queryAlias}Right")
            ->andWhere("{$queryAlias}.{$this->rootCol} = :{$queryAlias}Root")
            ->orderBy("{$queryAlias}.{$this->leftCol}")
            ->setParameter("{$queryAlias}Level", $nodeData['level'])
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
    public function createSubtreeQueryBuilder(
        string $tableExpression,
        string $queryAlias,
        string $rootColumnName,
        int $parentId
    ): QueryBuilder {
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
    public function createParentsQueryBuilder(
        string $tableExpression,
        string $queryAlias,
        string $rootColumnName,
        int $nodeId
    ): QueryBuilder {
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
    public function createSubtreeThroughMultipleNodesQueryBuilder(
        string $tableExpression,
        string $queryAlias,
        string $rootColumnName,
        array $nodeIds,
        int $minChildLevel = 1,
        int $pkType = Connection::PARAM_INT_ARRAY
    ): QueryBuilder {
        $this->setRootColName($rootColumnName, $this->connection);

        $directNodeSubSelect = $this
            ->createDirectNodeSubselectQuery($tableExpression, $queryAlias);

        $parentQuery = $this
            ->createParentAndSiblingChainSubselectQuery($tableExpression, $queryAlias, $directNodeSubSelect);
        $childrenQuery = $this
            ->createChildrenSubselectQuery($tableExpression, $queryAlias, $directNodeSubSelect);
        $rootQuery = $this
            ->createRootSubselectQuery($tableExpression, $queryAlias, $directNodeSubSelect);

        $idQuery = $this
            ->createPrimaryKeySubselectQuery($queryAlias, $childrenQuery, $parentQuery, $rootQuery);

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

    /**
     * @param string $tableExpression
     * @param string $queryAlias
     * @return QueryBuilder
     */
    private function createDirectNodeSubselectQuery(string $tableExpression, string $queryAlias): QueryBuilder
    {
        return $this->connection->createQueryBuilder()
            ->select([
                "{$queryAlias}directNode." . $this->pkCol,
                "{$queryAlias}directNode." . $this->leftCol,
                "{$queryAlias}directNode." . $this->rightCol,
                "{$queryAlias}directNode." . $this->levelCol,
                "{$queryAlias}directNode." . $this->rootCol,
            ])
            ->from($this->connection->quoteIdentifier($tableExpression), "{$queryAlias}directNode")
            ->andWhere("{$queryAlias}directNode.{$this->pkCol} IN (:{$queryAlias}nodeIds)");
    }

    /**
     * @param string $tableExpression
     * @param string $queryAlias
     * @param QueryBuilder $directNodeSubSelect
     * @return QueryBuilder
     */
    private function createParentAndSiblingChainSubselectQuery(
        string $tableExpression,
        string $queryAlias,
        QueryBuilder $directNodeSubSelect
    ): QueryBuilder {
        return $this->connection->createQueryBuilder()
            ->select([
                "{$queryAlias}SiblingNode." . $this->pkCol,
                "{$queryAlias}SiblingNode." . $this->leftCol,
                "{$queryAlias}SiblingNode." . $this->rightCol,
                "{$queryAlias}SiblingNode." . $this->levelCol,
                "{$queryAlias}SiblingNode." . $this->rootCol,
            ])
            ->from($this->connection->quoteIdentifier($tableExpression), "{$queryAlias}SiblingNode")
            ->innerJoin(
                "{$queryAlias}SiblingNode",
                $this->connection->quoteIdentifier($tableExpression),
                "{$queryAlias}ParentNode",
                "
                    {$queryAlias}SiblingNode.{$this->leftCol} >= {$queryAlias}ParentNode.{$this->leftCol} 
                AND {$queryAlias}SiblingNode.{$this->rightCol} <= {$queryAlias}ParentNode.{$this->rightCol} 
                AND {$queryAlias}SiblingNode.{$this->levelCol} = {$queryAlias}ParentNode.{$this->levelCol} + 1
                AND {$queryAlias}SiblingNode.{$this->rootCol} = {$queryAlias}ParentNode.{$this->rootCol}
                "
            )
            ->innerJoin(
                "{$queryAlias}ParentNode",
                '(' . $directNodeSubSelect->getSQL() . ')',
                "{$queryAlias}SelectedNode",
                "
                    {$queryAlias}ParentNode.{$this->leftCol} < {$queryAlias}SelectedNode.{$this->leftCol} 
                AND {$queryAlias}ParentNode.{$this->rightCol} > {$queryAlias}SelectedNode.{$this->rightCol} 
                AND {$queryAlias}ParentNode.{$this->rootCol} = {$queryAlias}SelectedNode.{$this->rootCol}
                "
            );
    }

    /**
     * @param string $tableExpression
     * @param string $queryAlias
     * @param QueryBuilder $directNodeSubSelect
     * @return QueryBuilder
     */
    private function createChildrenSubselectQuery(
        string $tableExpression,
        string $queryAlias,
        QueryBuilder $directNodeSubSelect
    ): QueryBuilder {
        return $this->connection->createQueryBuilder()
            ->select([
                "{$queryAlias}ChildNode." . $this->pkCol,
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
    }

    /**
     * @param string $tableExpression
     * @param string $queryAlias
     * @param QueryBuilder $directNodeSubSelect
     * @return QueryBuilder
     */
    private function createRootSubselectQuery(
        string $tableExpression,
        string $queryAlias,
        QueryBuilder $directNodeSubSelect
    ): QueryBuilder {
        return $this->connection->createQueryBuilder()
            ->select([
                "{$queryAlias}RootNode." . $this->pkCol,
                "{$queryAlias}RootNode." . $this->leftCol,
                "{$queryAlias}RootNode." . $this->rightCol,
                "{$queryAlias}RootNode." . $this->levelCol,
                "{$queryAlias}RootNode." . $this->rootCol,
            ])
            ->from($this->connection->quoteIdentifier($tableExpression), "{$queryAlias}RootNode")
            ->innerJoin(
                "{$queryAlias}RootNode",
                '(' . $directNodeSubSelect->getSQL() . ')',
                "{$queryAlias}SelectedNode",
                " 
                    {$queryAlias}RootNode.{$this->levelCol} = 0
                AND {$queryAlias}RootNode.{$this->rootCol} = {$queryAlias}SelectedNode.{$this->rootCol}
                "
            );
    }

    /**
     * @param string $queryAlias
     * @param QueryBuilder $childrenQuery
     * @param QueryBuilder $parentQuery
     * @param QueryBuilder $rootQuery
     * @return QueryBuilder
     */
    private function createPrimaryKeySubselectQuery(
        string $queryAlias,
        QueryBuilder $childrenQuery,
        QueryBuilder $parentQuery,
        QueryBuilder $rootQuery
    ): QueryBuilder {
        return $this->connection->createQueryBuilder()
            ->select("{$queryAlias}Group.{$this->pkCol}")
            ->from(
                '((' . $childrenQuery->getSQL() . ') UNION (' . $parentQuery->getSQL() . ') UNION (' . $rootQuery->getSQL() . ')) ',
                "{$queryAlias}Group"
            )
            ->groupBy("{$queryAlias}Group.id");
    }
}
