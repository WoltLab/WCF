<?php

namespace wcf\system\nodeTreeView\admin;

use wcf\data\category\Category;
use wcf\data\category\CategoryNode;
use wcf\data\category\CategoryNodeTree;
use wcf\data\DatabaseObject;
use wcf\data\IObjectTreeNode;
use wcf\data\object\type\ObjectType;
use wcf\system\category\CategoryHandler;
use wcf\system\category\ICategoryType;
use wcf\system\exception\InvalidObjectTypeException;
use wcf\system\interaction\admin\CategoryInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\interaction\ToggleInteraction;
use wcf\system\nodeTreeView\AbstractNodeTreeView;

/**
 * Abstract implementation of a node tree view that shows the categories of a
 * specific category object type.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
class CategoryNodeTreeView extends AbstractNodeTreeView
{
    private ?ObjectType $objectType = null;

    public function __construct(public readonly string $objectTypeName)
    {
        $provider = new CategoryInteractions();
        $provider->addInteractions([
            new Divider(),
            new class(
                '',
                static fn(Category $category) => $category->getObjectType()->getProcessor()->canEditCategory()
            ) extends EditInteraction {
                #[\Override]
                protected function getLink(DatabaseObject $object): string
                {
                    \assert($object instanceof Category);

                    return $object->getObjectType()->getProcessor()->getEditFormLink($object);
                }
            },
        ]);
        $this->setInteractionProvider($provider);

        $this->addQuickInteraction(
            new ToggleInteraction(
                'enable',
                'core/categories/%s/enable',
                'core/categories/%s/disable',
                isAvailableCallback: static fn(Category $category) => $category->getObjectType()
                    ->getProcessor()
                    ->canEditCategory()
            )
        );

        $this->setSetPositionsEndpoint(
            "core/categories/object-types/{$this->getObjectType()->objectTypeID}/positions"
        );
    }

    public function getObjectType(): ObjectType
    {
        if ($this->objectType === null) {
            $objectType = CategoryHandler::getInstance()->getObjectTypeByName($this->objectTypeName);
            if ($objectType === null) {
                throw new InvalidObjectTypeException($this->objectTypeName, 'com.woltlab.wcf.category');
            }
            $this->objectType = $objectType;
        }

        return $this->objectType;
    }

    public function getProcessor(): ICategoryType
    {
        return $this->getObjectType()->getProcessor();
    }

    #[\Override]
    protected function createNodeIterator(): \RecursiveIteratorIterator
    {
        $nodeTree = new CategoryNodeTree($this->objectTypeName, 0, true);

        // @phpstan-ignore return.type
        return $nodeTree->getIterator();
    }

    #[\Override]
    public function getNodeLink(IObjectTreeNode $node): string
    {
        \assert($node instanceof CategoryNode);

        return $this->getProcessor()->getEditFormLink($node->getDecoratedObject());
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return $this->getProcessor()->canEditCategory() || $this->getProcessor()->canDeleteCategory();
    }

    #[\Override]
    public function getParameters(): array
    {
        return ['objectTypeName' => $this->objectTypeName];
    }
}
