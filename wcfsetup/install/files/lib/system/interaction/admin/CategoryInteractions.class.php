<?php

namespace wcf\system\interaction\admin;

use wcf\data\category\Category;
use wcf\data\DatabaseObject;
use wcf\event\interaction\admin\CategoryInteractionCollecting;
use wcf\system\category\ICategoryType;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\interaction\LinkInteraction;

/**
 * Interaction provider for categories.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CategoryInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new class(
                'add-child-node',
                '',
                'wcf.category.addChildNode',
                static function (Category $category): bool {
                    $processor = $category->getObjectType()->getProcessor();
                    \assert($processor instanceof ICategoryType);
                    if (!$processor->canAddCategory()) {
                        return false;
                    }

                    if ($processor->getAddFormLink($category) === '') {
                        return false;
                    }

                    $maximumNestingLevel = $processor->getMaximumNestingLevel();
                    if ($maximumNestingLevel === 0) {
                        return false;
                    }

                    if ($maximumNestingLevel === -1) {
                        return true;
                    }

                    return \count($category->getParentCategories()) + 1 < $maximumNestingLevel;
                }
            ) extends LinkInteraction {
                #[\Override]
                protected function getLink(DatabaseObject $object): string
                {
                    \assert($object instanceof Category);

                    return $object->getObjectType()->getProcessor()->getAddFormLink($object);
                }
            },
            new DeleteInteraction(
                'core/categories/%s',
                static fn(Category $category) => $category->getObjectType()->getProcessor()->canDeleteCategory()
            ),
        ]);

        EventHandler::getInstance()->fire(
            new CategoryInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return Category::class;
    }
}
