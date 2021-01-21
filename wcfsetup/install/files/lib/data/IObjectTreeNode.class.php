<?php

namespace wcf\data;

/**
 * Every node of a database object tree has to implement this interface.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data
 * @since   5.2
 */
interface IObjectTreeNode extends \Countable, IIDObject, \RecursiveIterator
{
    /**
     * Adds the given node as child node and sets the child node's parent node to this node.
     *
     * @param   IObjectTreeNode     $child      added child node
     * @throws  \InvalidArgumentException       if given object is no (deocrated) instance of this class
     */
    public function addChild(self $child);

    /**
     * Returns the depth of the node within the tree.
     *
     * The minimum depth is `1`.
     *
     * @return  int
     */
    public function getDepth();

    /**
     * Returns the number of open parent nodes.
     *
     * @return  int
     */
    public function getOpenParentNodes();

    /**
     * Retruns the parent node of this node.
     *
     * @return  static      parent node
     */
    public function getParentNode();

    /**
     * Returns `true` if this node is the last sibling and `false` otherwise.
     *
     * @return  bool
     */
    public function isLastSibling();

    /**
     * Sets the parent node of this node.
     *
     * @param   IObjectTreeNode     $parentNode parent node
     * @throws  \InvalidArgumentException       if given object is no (deocrated) instance of this class
     */
    public function setParentNode(self $parentNode);
}
