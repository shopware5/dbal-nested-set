<?php declare(strict_types=1);

namespace Shopware\DbalNestedSet\Tool;

/**
 * Provides inspections for nodes and node relations
 *
 * @see NestedSetTableNodeInspector
 * @see NestedSetArrayNodeInspector
 */
class NestedSetNodeInspector
{
    /**
     * determines if node is leaf
     *
     * @param int $nodeLeftValue
     * @param int $nodeRightValue
     * @return bool
     */
    public function isLeaf(int $nodeLeftValue, int $nodeRightValue): bool
    {
        return (($nodeRightValue - $nodeLeftValue) === 1);
    }

    /**
     * determines if node is root
     *
     * @param int $nodeLeftValue
     * @return bool
     */
    public function isRoot(int $nodeLeftValue): bool
    {
        return ($nodeLeftValue === 1);
    }

    /**
     * determines if node is equal to subject node
     *
     * @param int $node1LeftValue
     * @param int $node1RightValue
     * @param int $node1RootValue
     * @param int $node2LeftValue
     * @param int $node2RightValue
     * @param int $node2RootValue
     * @return bool
     */
    public function isEqual(
        int $node1LeftValue,
        int $node1RightValue,
        int $node1RootValue,
        int $node2LeftValue,
        int $node2RightValue,
        int $node2RootValue
    ): bool {
        return (($node1LeftValue === $node2LeftValue) &&
            ($node1RightValue === $node2RightValue) &&
            ($node1RootValue === $node2RootValue)
        );
    }

    /**
     * determines if node is child of subject node
     *
     * @param int $node1LeftValue
     * @param int $node1RightValue
     * @param int $node1RootValue
     * @param int $node2LeftValue
     * @param int $node2RightValue
     * @param int $node2RootValue
     * @return bool
     */
    public function isDescendant(
        int $node1LeftValue,
        int $node1RightValue,
        int $node1RootValue,
        int $node2LeftValue,
        int $node2RightValue,
        int $node2RootValue
    ): bool {
        return (($node1LeftValue > $node2LeftValue) &&
            ($node1RightValue < $node2RightValue) &&
            ($node1RootValue === $node2RootValue)
        );
    }

    /**
     * determines if node is child of or sibling to subject node
     *
     * @param int $node1LeftValue
     * @param int $node1RightValue
     * @param int $node1RootValue
     * @param int $node2LeftValue
     * @param int $node2RightValue
     * @param int $node2RootValue
     * @return bool
     */
    public function isDescendantOrEqual(
        int $node1LeftValue,
        int $node1RightValue,
        int $node1RootValue,
        int $node2LeftValue,
        int $node2RightValue,
        int $node2RootValue
    ): bool {
        return (($node1LeftValue >= $node2LeftValue) &&
            ($node1RightValue <= $node2RightValue) &&
            ($node1RootValue === $node2RootValue)
        );
    }

    /**
     * determines if node is ancestor of subject node
     *
     * @param int $node1LeftValue
     * @param int $node1RightValue
     * @param int $node1RootValue
     * @param int $node2LeftValue
     * @param int $node2RightValue
     * @param int $node2RootValue
     * @return bool
     */
    public function isAncestor(
        int $node1LeftValue,
        int $node1RightValue,
        int $node1RootValue,
        int $node2LeftValue,
        int $node2RightValue,
        int $node2RootValue
    ): bool {
        return (($node1LeftValue < $node2LeftValue) &&
            ($node1RightValue > $node2RightValue) &&
            ($node1RootValue === $node2RootValue)
        );
    }
}
