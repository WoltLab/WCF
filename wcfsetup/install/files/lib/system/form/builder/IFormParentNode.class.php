<?php

namespace wcf\system\form\builder;

/**
 * Represents a form node that can have child nodes.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
interface IFormParentNode extends \Countable, IFormNode, \RecursiveIterator
{
    /**
     * Appends the given node to this node and returns this node.
     *
     * @return  $this
     *
     * @throws  \BadMethodCallException     if method is called with multiple `IFormChildNode` as parameter (if mistakenly used instead of `appendChildren()`)
     */
    public function appendChild(IFormChildNode $child): static;

    /**
     * Appends the given children to this node and returns this node.
     *
     * @param IFormChildNode[] $children
     * @return  $this
     */
    public function appendChildren(array $children): static;

    /**
     * Returns all child nodes of this node.
     *
     * @return  IFormChildNode[]
     */
    public function children(): array;

    /**
     * Returns `true` if this node (or any of the child nodes) contains the node
     * with the given id and returns `false` otherwise.
     */
    public function contains(string $nodeId): bool;

    /**
     * Returns a recursive iterator for this node.
     *
     * Note: A class cannot implement `\Iterator` and `\IteratorAggregate` at the same time.
     *
     * @return  \RecursiveIteratorIterator  recursive iterator for this node
     */
    public function getIterator();

    /**
     * Returns the node with the given id or `null` if no such node exists.
     *
     * All descendants, not only the direct child nodes, are checked to find the
     * requested node.
     *
     * @throws  \InvalidArgumentException   if the given id is invalid
     */
    public function getNodeById(string $nodeId): ?IFormNode;

    /**
     * Returns `true` if this node or any of its children has a validation error and
     * return `false` otherwise.
     */
    public function hasValidationErrors(): bool;

    /**
     * Inserts the given node after the node with the given id and returns this node.
     *
     * @param $child inserted child node
     * @param $referenceNodeId id of the node after which the given node is inserted
     * @return  $this
     *
     * @throws  \InvalidArgumentException           if given node cannot be inserted or reference node id is invalid
     */
    public function insertAfter(IFormChildNode $child, string $referenceNodeId): static;

    /**
     * Inserts the given node before the node with the given id and returns this node.
     *
     * @param $child inserted child node
     * @param $referenceNodeId id of the node before which the given node is inserted
     * @return  $this
     *
     * @throws  \InvalidArgumentException           if given node cannot be inserted or reference node id is invalid
     */
    public function insertBefore(IFormChildNode $child, string $referenceNodeId): static;

    /**
     * Reads the value of this node and its children from request data and
     * return this field.
     *
     * @return  $this
     */
    public function readValues(): static;

    /**
     * Checks if the given node is a valid child for this node.
     *
     * @param $child validated child node
     *
     * @throws  \InvalidArgumentException       if given node cannot is an invalid child
     */
    public function validateChild(IFormChildNode $child): void;
}
