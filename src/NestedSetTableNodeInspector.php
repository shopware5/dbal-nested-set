<?php declare(strict_types=1);

namespace Shopware\DbalNestedSet;

use Shopware\DbalNestedSet\Tool\NestedSetArrayNodeInspector;
use Shopware\DbalNestedSet\Tool\NestedSetReader;

class NestedSetTableNodeInspector
{
    /**
     * @var NestedSetArrayNodeInspector
     */
    private $inspector;

    /**
     * @var NestedSetReader
     */
    private $reader;

    /**
     * @param NestedSetArrayNodeInspector $inspector
     * @param NestedSetReader $reader
     */
    public function __construct(NestedSetArrayNodeInspector $inspector, NestedSetReader $reader)
    {
        $this->inspector = $inspector;
        $this->reader = $reader;
    }


    /**
     * determines if node is leaf
     *
     * @param string $tableExpression
     * @param string $rootColumnName
     * @param int $nodeId
     * @return bool
     */
    public function isLeaf(string $tableExpression, string $rootColumnName, int $nodeId): bool
    {
        $node = $this->reader->fetchNodeData($tableExpression, $rootColumnName, $nodeId);

        return $this->inspector->isLeaf($node);
    }

    /**
     * determines if node is root
     *
     * @param string $tableExpression
     * @param string $rootColumnName
     * @param int $nodeId
     * @return bool
     */
    public function isRoot(string $tableExpression, string $rootColumnName, int $nodeId): bool
    {
        $node = $this->reader->fetchNodeData($tableExpression, $rootColumnName, $nodeId);

        return $this->inspector->isRoot($node);
    }

    /**
     * determines if node is child of subject node
     *
     * @param string $tableExpression
     * @param string $rootColumnName
     * @param int $node1Id
     * @param int $node2Id
     * @return bool
     */
    public function isDescendant(string $tableExpression, string $rootColumnName, int $node1Id, int $node2Id): bool
    {
        $node1 = $this->reader->fetchNodeData($tableExpression, $rootColumnName, $node1Id);
        $node2 = $this->reader->fetchNodeData($tableExpression, $rootColumnName, $node2Id);

        return $this->inspector->isDescendant($node1, $node2);
    }

    /**
     * determines if node is child of or sibling to subject node
     *
     * @param string $tableExpression
     * @param string $rootColumnName
     * @param int $node1Id
     * @param int $node2Id
     * @return bool
     */
    public function isDescendantOfOrEqual(string $tableExpression, string $rootColumnName, int $node1Id, int $node2Id): bool
    {
        $node1 = $this->reader->fetchNodeData($tableExpression, $rootColumnName, $node1Id);
        $node2 = $this->reader->fetchNodeData($tableExpression, $rootColumnName, $node2Id);

        return $this->inspector->isDescendantOrEqual($node1, $node2);
    }

    /**
     * determines if node is ancestor of subject node
     *
     * @param string $tableExpression
     * @param string $rootColumnName
     * @param int $node1Id
     * @param int $node2Id
     * @return bool
     */
    public function isAncestor(string $tableExpression, string $rootColumnName, int $node1Id, int $node2Id): bool
    {
        $node1 = $this->reader->fetchNodeData($tableExpression, $rootColumnName, $node1Id);
        $node2 = $this->reader->fetchNodeData($tableExpression, $rootColumnName, $node2Id);

        return $this->inspector->isAncestor($node1, $node2);
    }
}