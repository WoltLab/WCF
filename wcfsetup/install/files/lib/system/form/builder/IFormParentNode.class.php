<?php

namespace wcf\system\form\builder;

/**
 * Represents a form node that can have child nodes.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Form\Builder
 * @since   5.2
 */
interface IFormParentNode extends \Countable, IFormNode, \RecursiveIterator
{
    /**
     * Appends the given node to this node and returns this node.
     *
     * @param   IFormChildNode      $child      appended child
     * @return  static                  this node
     *
     * @throws  \BadMethodCallException     if method is called with multiple `IFormChildNode` as parameter (if mistakenly used instead of `appendChildren()`)
     */
    public function appendChild(IFormChildNode $child);

    /**
     * Appends the given children to this node and returns this node.
     *
     * @param   IFormChildNode[]    $children   appended children
     * @return  static                  this node
     */
    public function appendChildren(array $children);

    /**
     * Returns all child nodes of this node.
     *
     * @return  IFormChildNode[]    children of this node
     */
    public function children();

    /**
     * Returns `true` if this node (or any of the child nodes) contains the node
     * with the given id and returns `false` otherwise.
     *
     * @param   string      $nodeId     id of searched node
     * @return  bool
     */
    public function contains($nodeId);

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
     * @param   string      $nodeId     id of the requested node
     * @return  null|IFormNode          requested node
     *
     * @throws  \InvalidArgumentException   if the given id is invalid
     */
    public function getNodeById($nodeId);

    /**
     * Returns `true` if this node or any of its children has a validation error and
     * return `false` otherwise.
     *
     * @return  bool
     */
    public function hasValidationErrors();

    /**
     * Inserts the given node after the node with the given id and returns this node.
     *
     * @param   IFormChildNode      $child          inserted child node
     * @param   string          $referenceNodeId    id of the node after which the given node is inserted
     * @return  static                      this node
     *
     * @throws  \InvalidArgumentException           if given node cannot be inserted or reference node id is invalid
     */
    public function insertAfter(IFormChildNode $child, $referenceNodeId);

    /**
     * Inserts the given node before the node with the given id and returns this node.
     *
     * @param   IFormChildNode      $child          inserted child node
     * @param   string          $referenceNodeId    id of the node before which the given node is inserted
     * @return  static                      this node
     *
     * @throws  \InvalidArgumentException           if given node cannot be inserted or reference node id is invalid
     */
    public function insertBefore(IFormChildNode $child, $referenceNodeId);

    /**
     * Reads the value of this node and its children from request data and
     * return this field.
     *
     * @return  static      this node
     */
    public function readValues();

    /**
     * Checks if the given node is a valid child for this node.
     *
     * @param   IFormChildNode      $child      validated child node
     *
     * @throws  \InvalidArgumentException       if given node cannot is an invalid child
     */
    public function validateChild(IFormChildNode $child);
}
