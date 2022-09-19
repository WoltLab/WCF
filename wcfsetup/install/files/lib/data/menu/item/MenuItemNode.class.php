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
     */
    protected int $depth = 0;

    /**
     * true if item or one of its children is active
     */
    protected bool $isActive = false;

    /**
     * parent node
     * @var MenuItemNode
     */
    protected $parentNode;

    /**
     * iterator position
     * @var int
     */
    private int $position = 0;

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
    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    /**
     * Returns the parent node
     */
    public function getParentNode(): MenuItemNode
    {
        return $this->parentNode;
    }

    /**
     * Returns the number of children.
     */
    public function count(): int
    {
        return \count($this->children);
    }

    /**
     * Returns true if this element is the last sibling.
     */
    public function isLastSibling(): bool
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
     */
    public function getOpenParentNodes(): int
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
    public function setIsActive(): void
    {
        $this->isActive = true;

        // propagate active state to immediate parent
        if ($this->parentNode) {
            $this->parentNode->setIsActive();
        }
    }

    /**
     * Returns true if this item (or one of its children) is marked as active.
     */
    public function isActiveNode(): bool
    {
        return $this->isActive;
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return isset($this->children[$this->position]);
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        $this->position++;
    }

    /**
     * @inheritDoc
     */
    public function current(): MenuItemNode
    {
        return $this->children[$this->position];
    }

    /**
     * @inheritDoc
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * @inheritDoc
     */
    public function getChildren(): MenuItemNode
    {
        return $this->children[$this->position];
    }

    /**
     * @inheritDoc
     */
    public function hasChildren(): bool
    {
        return \count($this->children) > 0;
    }

    /**
     * Returns node depth.
     */
    public function getDepth(): int
    {
        return $this->depth;
    }
}
