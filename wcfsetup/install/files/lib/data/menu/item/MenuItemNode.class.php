<?php

namespace wcf\data\menu\item;

use wcf\data\DatabaseObjectDecorator;
use wcf\data\IObjectTreeNode;
use wcf\data\TObjectTreeNode;

/**
 * Represents a menu item node element.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @mixin   MenuItem
 * @extends DatabaseObjectDecorator<MenuItem>
 */
class MenuItemNode extends DatabaseObjectDecorator implements IObjectTreeNode
{
    use TObjectTreeNode;

    /**
     * node depth
     */
    protected int $depth = 0;

    /**
     * true if item or one of its children is active
     */
    protected bool $isActive = false;

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

        // @phpstan-ignore assign.propertyType
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
        // @phpstan-ignore assign.propertyType
        $this->children = $children;
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
     * Returns node depth.
     */
    #[\Override]
    public function getDepth(): int
    {
        return $this->depth;
    }
}
