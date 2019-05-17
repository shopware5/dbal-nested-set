<?php declare(strict_types=1);

namespace Shopware\DbalNestedSet\Tool;

/**
 * expects an array as node as provided by NestedSetReader
 */
class NestedSetArrayNodeInspector
{
    /**
     * @var NestedSetNodeInspector
     */
    private $inspector;

    public function __construct(NestedSetNodeInspector $inspector)
    {
        $this->inspector = $inspector;
    }

    /**
     * determines if node is leaf
     */
    public function isLeaf(array $node): bool
    {
        return $this->inspector->isLeaf($node['left'], $node['right']);
    }

    /**
     * determines if node is root
     */
    public function isRoot(array $node): bool
    {
        return $this->inspector->isRoot($node['left']);
    }

    /**
     * determines if node is root
     */
    public function isEqual(array $node1, array $node2): bool
    {
        return $this->inspector->isEqual(
            $node1['left'], $node1['right'], $node1['root_id'],
            $node2['left'], $node2['right'], $node2['root_id']
        );
    }

    /**
     * determines if node is child of subject node
     */
    public function isDescendant(array $node1, array $node2): bool
    {
        return $this->inspector->isDescendant(
            $node1['left'], $node1['right'], $node1['root_id'],
            $node2['left'], $node2['right'], $node2['root_id']
        );
    }

    /**
     * determines if node is child of or sibling to subject node
     */
    public function isDescendantOrEqual(array $node1, array $node2): bool
    {
        return $this->inspector->isDescendantOrEqual(
            $node1['left'], $node1['right'], $node1['root_id'],
            $node2['left'], $node2['right'], $node2['root_id']
        );
    }

    /**
     * determines if node is ancestor of subject node
     */
    public function isAncestor(array $node1, array $node2): bool
    {
        return $this->inspector->isAncestor(
            $node1['left'], $node1['right'], $node1['root_id'],
            $node2['left'], $node2['right'], $node2['root_id']
        );
    }
}
