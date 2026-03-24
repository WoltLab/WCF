<?php

namespace wcf\data\page;

use wcf\data\DatabaseObjectDecorator;
use wcf\data\IObjectTreeNode;
use wcf\data\TObjectTreeNode;

/**
 * Represents a page node element.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin   Page
 * @extends DatabaseObjectDecorator<Page>
 */
class PageNode extends DatabaseObjectDecorator implements IObjectTreeNode
{
    use TObjectTreeNode;

    /**
     * node depth
     */
    protected int $depth = 0;

    /**
     * @inheritDoc
     */
    protected static $baseClass = Page::class;

    /**
     * Creates a new PageNode object.
     *
     * @param PageNode $parentNode
     */
    public function __construct($parentNode = null, ?Page $page = null, int $depth = 0)
    {
        if ($page === null) {
            $page = new Page(null, []);
        }
        parent::__construct($page);

        // @phpstan-ignore assign.propertyType
        $this->parentNode = $parentNode;
        $this->depth = $depth;
    }

    /**
     * Sets the children of this node.
     *
     * @param PageNode[] $children
     */
    public function setChildren(array $children): void
    {
        // @phpstan-ignore assign.propertyType
        $this->children = $children;
    }

    #[\Override]
    public function getDepth(): int
    {
        return $this->depth;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->getDecoratedObject()->name;
    }
}
