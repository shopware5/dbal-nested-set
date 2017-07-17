<?php declare(strict_types=1);

namespace Shopware\DbalNestedSet;

/**
 * expects an array as node as provided by NestedSetReader
 */
class NestedSetNodeInspectorArrayFacade
{
    /**
     * @var NestedSetNodeInspector
     */
    private $inspector;

    /**
     * @param NestedSetNodeInspector $inspector
     * @param NestedSetReader $reader
     */
    public function __construct(NestedSetNodeInspector $inspector)
    {
        $this->inspector = $inspector;
    }


    /**
     * determines if node is leaf
     *
     * @param array $node
     * @return bool
     */
    public function isLeaf(array $node): bool
    {
        return $this->inspector->isLeaf($node['left'], $node['right']);
    }

    /**
     * determines if node is root
     *
     * @param array $node
     * @return bool
     */
    public function isRoot(array $node): bool
    {
        return $this->inspector->isRoot($node['left']);
    }

    /**
     * determines if node is root
     *
     * @param array $node1
     * @param array $node2
     * @return bool
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
     *
     * @param array $node1
     * @param array $node2
     * @return bool
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
     *
     * @param array $node1
     * @param array $node2
     * @return bool
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
     *
     * @param array $node1
     * @param array $node2
     * @return bool
     */
    public function isAncestor(array $node1, array $node2): bool
    {
        return $this->inspector->isAncestor(
            $node1['left'], $node1['right'], $node1['root_id'],
            $node2['left'], $node2['right'], $node2['root_id']
        );
    }
}