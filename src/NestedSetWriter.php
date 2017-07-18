<?php declare(strict_types=1);

namespace Shopware\DbalNestedSet;

use Doctrine\DBAL\Connection;
use Shopware\DbalNestedSet\Tool\NestedSetArrayNodeInspector;
use Shopware\DbalNestedSet\Tool\NestedSetConfigAware;
use Shopware\DbalNestedSet\Tool\NestedSetReader;

class NestedSetWriter
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
     * @var NestedSetArrayNodeInspector
     */
    private $inspector;

    /**
     * @param Connection $connection
     * @param NestedSetReader $reader
     * @param NestedSetArrayNodeInspector $inspector
     * @param NestedSetConfig $conventionsConfig
     */
    public function __construct(Connection $connection, NestedSetReader $reader, NestedSetArrayNodeInspector $inspector, NestedSetConfig $conventionsConfig)
    {
        $this->connection = $connection;
        $this->reader = $reader;
        $this->setUpWithConnection($conventionsConfig, $connection);
        $this->inspector = $inspector;
    }

    /**
     * @param string $tableExpression
     * @param string $rootColumnName
     * @param int $rootId
     * @param array $data
     * @param array $types
     * @return int
     */
    public function insertRoot(string $tableExpression, string $rootColumnName, int $rootId, array $data, array $types = []): int
    {
        $this->setRootColName($rootColumnName, $this->connection);

        $nestedSetRootData = $this
            ->createNestedSetData(1, 2, 0, $rootId);

        $this->connection
            ->insert(
                $tableExpression,
                array_merge(
                    $nestedSetRootData,
                    $data
                ),
                $types
            );

        return (int) $this->connection
            ->lastInsertId();
    }

    /**
     * @see Connection::insert()
     *
     * @param string $tableExpression
     * @param string $rootColumnName
     * @param int $parentId
     * @param array $data
     * @param array $types
     * @return int id of the new node
     */
    public function insertAsFirstChild(string $tableExpression, string $rootColumnName, int $parentId, array $data, array $types = array()): int
    {
        $this->setRootColName($rootColumnName, $this->connection);
        $parentData = $this->reader->fetchNodeData($tableExpression, $rootColumnName, $parentId);

        $newLeft = $parentData['left'] + 1;

        $this->applyDeltaToSubsequendNodes($tableExpression, $parentData['root_id'], $newLeft, 2);

        $newNodeNestedSetData = $this->createNestedSetData(
            $newLeft,
            $newLeft + 1,
            $parentData['level'] + 1,
            $parentData['root_id']
        );

        return $this
            ->doInsert($tableExpression, $data, $types, $newNodeNestedSetData);
    }

    /**
     * @see Connection::insert()
     *
     * @param string $tableExpression
     * @param string $rootColumnName
     * @param int $parentId
     * @param array $data
     * @param array $types
     * @return int id of the new node
     */
    public function insertAsLastChild(string $tableExpression, string $rootColumnName, int $parentId, array $data, array $types = array()): int
    {
        $this->setRootColName($rootColumnName, $this->connection);
        $parentData = $this->reader->fetchNodeData($tableExpression, $rootColumnName, $parentId);

        $newLeft = $parentData['right'];

        $this->applyDeltaToSubsequendNodes($tableExpression, $parentData['root_id'], $newLeft, 2);

        $newNodeNestedSetData = $this->createNestedSetData(
            $newLeft,
            $newLeft + 1,
            $parentData['level'] + 1,
            $parentData['root_id']
        );

        return $this
            ->doInsert($tableExpression, $data, $types, $newNodeNestedSetData);
    }

    /**
     * @param string $tableExpression
     * @param string $rootColumnName
     * @param int $siblingId
     * @param array $data
     * @param array $types
     * @return int
     */
    public function insertAsPrevSibling(string $tableExpression, string $rootColumnName, int $siblingId, array $data, array $types = array()): int
    {
        $this->setRootColName($rootColumnName, $this->connection);
        $childData = $this->reader->fetchNodeData($tableExpression, $rootColumnName, $siblingId);
        $newLeft  = $childData['left'];
        $newRight = $childData['left'] + 1;

        $this->applyDeltaToSubsequendNodes($tableExpression, $childData['root_id'], $newLeft, 2);

        $newNodeNestedSetData = $this->createNestedSetData(
            $newLeft,
            $newRight,
            $childData['level'],
            $childData['root_id']
        );

        return $this
            ->doInsert($tableExpression, $data, $types, $newNodeNestedSetData);
    }

    /**
     * @param string $tableExpression
     * @param string $rootColumnName
     * @param int $siblingId
     * @param array $data
     * @param array $types
     * @return int
     */
    public function insertAsNextSibling(string $tableExpression, string $rootColumnName, int $siblingId, array $data, array $types = array()): int
    {
        $this->setRootColName($rootColumnName, $this->connection);
        $childData = $this->reader->fetchNodeData($tableExpression, $rootColumnName, $siblingId);
        $newLeft  = $childData['right'] + 1;
        $newRight = $childData['right'] + 2;

        $this->applyDeltaToSubsequendNodes($tableExpression, $childData['root_id'], $newLeft, 2);

        $newNodeNestedSetData = $this->createNestedSetData(
            $newLeft,
            $newRight,
            $childData['level'],
            $childData['root_id']
        );

        return $this
            ->doInsert($tableExpression, $data, $types, $newNodeNestedSetData);
    }

    /**
     * @param string $tableExpression
     * @param string $rootColumnName
     * @param int $parentId
     * @param int $childId
     * @throws NestedSetExceptionInvalidNodeOperation
     */
    public function moveAsLastChild(string $tableExpression, string $rootColumnName, int $parentId, int $childId)
    {
        $this->setRootColName($rootColumnName, $this->connection);

        $parent = $this->reader->fetchNodeData($tableExpression, $rootColumnName, $parentId);
        $child = $this->reader->fetchNodeData($tableExpression, $rootColumnName, $childId);


        if (
            $this->inspector->isEqual($parent, $child) ||
            $this->inspector->isAncestor($child, $parent)
        ) {
            throw new NestedSetExceptionInvalidNodeOperation('Cannot move node as last child of itself or into a descendant');
        }

        $level = ($parent['level'] + 1) - $child['level'];

        $this->updateNodePosition($tableExpression, $child, $parent['right'], $level);
    }

    /**
     * @param string $tableExpression
     * @param string $rootColumnName
     * @param int $parentId
     * @param int $childId
     * @throws NestedSetExceptionInvalidNodeOperation
     */
    public function moveAsFirstChild(string $tableExpression, string $rootColumnName, int $parentId, int $childId)
    {
        $this->setRootColName($rootColumnName, $this->connection);

        $parent = $this->reader->fetchNodeData($tableExpression, $rootColumnName, $parentId);
        $child = $this->reader->fetchNodeData($tableExpression, $rootColumnName, $childId);

        if (
            $this->inspector->isEqual($parent, $child) ||
            $this->inspector->isAncestor($child, $parent)
        ) {
            throw new NestedSetExceptionInvalidNodeOperation('Cannot move node as first child of itself or into a descendant');
        }

        $level = ($parent['level'] + 1) - $child['level'];

        $this->updateNodePosition($tableExpression, $child, $parent['left'] + 1, $level);
    }

    /**
     * @param string $tableExpression
     * @param string $rootColumnName
     * @param int $siblingId
     * @param int $childId
     * @throws NestedSetExceptionInvalidNodeOperation
     */
    public function moveAsPrevSibling(string $tableExpression, string $rootColumnName, int $siblingId, int $childId)
    {
        $this->setRootColName($rootColumnName, $this->connection);

        $sibling = $this->reader->fetchNodeData($tableExpression, $rootColumnName, $siblingId);
        $child = $this->reader->fetchNodeData($tableExpression, $rootColumnName, $childId);

        if (
            $this->inspector->isEqual($sibling, $child) ||
            $this->inspector->isAncestor($child, $sibling)
        ) {
            throw new NestedSetExceptionInvalidNodeOperation('Cannot move node as prev sibling of itself or into a descendant');
        }

        $level = $sibling['level'] - $child['level'];

        $this->updateNodePosition($tableExpression, $child, $sibling['left'], $level);
    }

    /**
     * @param string $tableExpression
     * @param string $rootColumnName
     * @param int $siblingId
     * @param int $childId
     * @throws NestedSetExceptionInvalidNodeOperation
     */
    public function moveAsNextSibling(string $tableExpression, string $rootColumnName, int $siblingId, int $childId)
    {
        $this->setRootColName($rootColumnName, $this->connection);

        $sibling = $this->reader->fetchNodeData($tableExpression, $rootColumnName, $siblingId);
        $child = $this->reader->fetchNodeData($tableExpression, $rootColumnName, $childId);

        if (
            $this->inspector->isEqual($sibling, $child) ||
            $this->inspector->isAncestor($child, $sibling)
        ) {
            throw new NestedSetExceptionInvalidNodeOperation('Cannot move node as next sibling of itself or into a descendant');
        }

        $level = $sibling['level'] - $child['level'];

        $this->updateNodePosition($tableExpression, $child, $sibling['right'] + 1, $level);
    }

    /**
     * @param string $tableExpression
     * @param string $rootColumnName
     * @param int $nodeId
     */
    public function removeNode(string $tableExpression, string $rootColumnName, int $nodeId)
    {
        $this->setRootColName($rootColumnName, $this->connection);
        $node = $this->reader->fetchNodeData($tableExpression, $rootColumnName, $nodeId);

        $this->connection
            ->executeUpdate(
                "DELETE FROM {$tableExpression} WHERE {$this->leftCol} >= :left AND {$this->rightCol} <= :right",
                ['left' => $node['left'], 'right' => $node['right']]
            );

        $first = $node['right'] + 1;
        $delta = $node['left'] - $node['right'] - 1;
        $this->applyDeltaToSubsequendNodes($tableExpression, $node['root_id'], $first, $delta);
    }

    /**
     * @param int $leftValue
     * @param int $rightValue
     * @param int $levelValue
     * @param int $rootId
     * @return array
     */
    private function createNestedSetData(int $leftValue, int $rightValue, int $levelValue, int $rootId): array
    {
        return [
            $this->leftCol => $leftValue,
            $this->rightCol => $rightValue,
            $this->levelCol => $levelValue,
            $this->rootCol => $rootId,
        ];
    }

    /**
     * @param string $tableExpression
     * @param array $data
     * @param array $types
     * @param $newNodeNestedSetData
     * @return int
     */
    private function doInsert(string $tableExpression, array $data, array $types, $newNodeNestedSetData): int
    {
        $this->connection
            ->insert(
                $tableExpression,
                array_merge($data, $newNodeNestedSetData),
                $types
            );

        return (int) $this->connection
            ->lastInsertId();
    }

    /**
     * move node's and its children to location $destLeft and updates rest of tree
     *
     * @param string $tableExpression
     * @param array $nodeData
     * @param int $destLeft destination left value
     * @param $levelDiff
     */
    private function updateNodePosition(string $tableExpression, array $nodeData, $destLeft, $levelDiff)
    {
        $left = $nodeData['left'];
        $right = $nodeData['right'];
        $treeSize = $right - $left + 1;

        // Make room in the new branch
        $this->applyDeltaToSubsequendNodes($tableExpression, $nodeData['root_id'], $destLeft, $treeSize);

        if ($left >= $destLeft) { // src was shifted too?
            $left += $treeSize;
            $right += $treeSize;
        }

        // update level for descendants
        $this->connection->createQueryBuilder()
            ->update($tableExpression)
            ->set($this->levelCol, "{$this->levelCol} + :level")
            ->where("{$this->leftCol}> :left AND {$this->rightCol} < :right")
            ->andWhere("{$this->rootCol} = :rootValue")
            ->setParameters([
                'level' => $levelDiff,
                'left' =>$left,
                'right' => $right,
                'rootValue' => $nodeData['root_id'],
            ])
            ->execute();

        // now there's enough room next to target to move the subtree
        $this->applyDeltaToSubtree($tableExpression, $nodeData['root_id'], $left, $right, $destLeft - $left);

        // correct values after source (close gap in old tree)
        $this->applyDeltaToSubsequendNodes($tableExpression, $nodeData['root_id'], $right + 1, -$treeSize);
    }

    /**
     * adds '$delta' to all Left and Right values that are >= '$first'. '$delta' can also be negative.
     *
     * Note: This method does wrap its database queries in a transaction. This should be done
     * by the invoking code.
     *
     * @param string $tableExpression
     * @param int $rootValue
     * @param int $first First node to be shifted
     * @param int $delta Value to be shifted by, can be negative
     */
    private function applyDeltaToSubsequendNodes(string $tableExpression, int $rootValue, int $first, int $delta)
    {
        $this->connection->createQueryBuilder()
            ->update($tableExpression)
            ->set($this->leftCol, "{$this->leftCol}+ :delta")
            ->where("{$this->leftCol} >= :first")
            ->andWhere("{$this->rootCol} = :rootValue")
            ->setParameters([
                'delta' => $delta,
                'first' => $first,
                'rootValue' => $rootValue,
            ])
            ->execute();

        $this->connection->createQueryBuilder()
            ->update($tableExpression)
            ->set($this->rightCol, "{$this->rightCol} + :delta")
            ->where("{$this->rightCol} >= :first")
            ->andWhere("{$this->rootCol} = :rootValue")
            ->setParameters([
                'delta' => $delta,
                'first' => $first,
                'rootValue' => $rootValue,
            ])
            ->execute();
    }

    /**
     * adds '$delta' to all Left and Right values that are >= '$first' and <= '$last'.
     * '$delta' can also be negative.
     *
     * Note: This method does wrap its database queries in a transaction. This should be done
     * by the invoking code.
     *
     * @param string $tableExpression
     * @param int $rootValue
     * @param int $first First node to be shifted (L value)
     * @param int $last Last node to be shifted (L value)
     * @param int $delta Value to be shifted by, can be negative
     */
    private function applyDeltaToSubtree(string $tableExpression, int $rootValue, int $first, int $last, int $delta)
    {
        $this->connection->createQueryBuilder()
            ->update($tableExpression)
            ->set($this->leftCol, "{$this->leftCol} + :delta")
            ->where("{$this->leftCol} >= :first AND {$this->leftCol} <= :last")
            ->andWhere("{$this->rootCol} = :rootValue")
            ->setParameters([
                'delta' => $delta,
                'first' => $first,
                'last' => $last,
                'rootValue' => $rootValue,
            ])
            ->execute();

        $this->connection->createQueryBuilder()
            ->update($tableExpression)
            ->set($this->rightCol, "{$this->rightCol} + :delta")
            ->where("{$this->rightCol} >= :first AND {$this->rightCol} <= :last")
            ->andWhere("{$this->rootCol} = :rootValue")
            ->setParameters([
                'delta' => $delta,
                'first' => $first,
                'last' => $last,
                'rootValue' => $rootValue,
            ])
            ->execute();
    }
}
