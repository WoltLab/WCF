<?php

namespace wcf\data\menu\item;

use wcf\data\DatabaseObjectDecorator;

/**
 * Represents a menu item node element.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\Menu\Item
 * @since   3.0
 *
 * @method  MenuItem    getDecoratedObject()
 * @mixin   MenuItem
 */
class MenuItemNode extends DatabaseObjectDecorator implements \Countable, \RecursiveIterator
{
    /**
     * children of this node
     * @var MenuItemNode[]
     */
    protected $children = [];

    /**
     * node depth
     * @var int
     */
    protected $depth = 0;

    /**
     * true if item or one of its children is active
     * @var bool
     */
    protected $isActive = false;

    /**
     * parent node
     * @var MenuItemNode
     */
    protected $parentNode;

    /**
     * iterator position
     * @var int
     */
    private $position = 0;

    /**
     * @inheritDoc
     */
    protected static $baseClass = MenuItem::class;

    /**
     * Creates a new MenuItemNode object.
     *
     * @param MenuItemNode $parentNode
     * @param MenuItem $menuItem
     * @param int $depth
     */
    public function __construct($parentNode = null, ?MenuItem $menuItem = null, $depth = 0)
    {
        if ($menuItem === null) {
            $menuItem = new MenuItem(null, []);
        }
        parent::__construct($menuItem);

        $this->parentNode = $parentNode;
        $this->depth = $depth;
    }

    /**
     * Sets the children of this node.
     *
     * @param MenuItemNode[] $children
     */
    public function setChildren(array $children)
    {
        $this->children = $children;
    }

    /**
     * Returns the parent node
     *
     * @return  MenuItemNode
     */
    public function getParentNode()
    {
        return $this->parentNode;
    }

    /**
     * Returns the number of children.
     *
     * @return  int
     */
    public function count()
    {
        return \count($this->children);
    }

    /**
     * Returns true if this element is the last sibling.
     *
     * @return  bool
     */
    public function isLastSibling()
    {
        foreach ($this->parentNode as $key => $child) {
            if ($child === $this) {
                if ($key == \count($this->parentNode) - 1) {
                    return true;
                }

                return false;
            }
        }
    }

    /**
     * Returns the number of open parent nodes.
     *
     * @return  int
     */
    public function getOpenParentNodes()
    {
        $element = $this;
        $i = 0;

        while ($element->parentNode->parentNode != null && $element->isLastSibling()) {
            $i++;
            $element = $element->parentNode;
        }

        return $i;
    }

    /**
     * Marks this item and all its direct ancestors as active.
     */
    public function setIsActive()
    {
        $this->isActive = true;

        // propagate active state to immediate parent
        if ($this->parentNode) {
            $this->parentNode->setIsActive();
        }
    }

    /**
     * Returns true if this item (or one of its children) is marked as active.
     *
     * @return  bool
     */
    public function isActiveNode()
    {
        return $this->isActive;
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * @inheritDoc
     */
    public function valid()
    {
        return isset($this->children[$this->position]);
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * @inheritDoc
     */
    public function current()
    {
        return $this->children[$this->position];
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @inheritDoc
     */
    public function getChildren()
    {
        return $this->children[$this->position];
    }

    /**
     * @inheritDoc
     */
    public function hasChildren()
    {
        return \count($this->children) > 0;
    }

    /**
     * Returns node depth.
     *
     * @return  int
     */
    public function getDepth()
    {
        return $this->depth;
    }
}
