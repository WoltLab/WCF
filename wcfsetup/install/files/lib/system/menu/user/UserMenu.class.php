<?php

namespace wcf\system\menu\user;

use wcf\data\user\menu\item\UserMenuItem;
use wcf\data\user\option\UserOption;
use wcf\system\cache\builder\UserMenuCacheBuilder;
use wcf\system\cache\builder\UserOptionCacheBuilder;
use wcf\system\menu\ITreeMenuItem;
use wcf\system\menu\TreeMenu;
use wcf\system\option\user\TUserOptionsByCategory;

/**
 * Builds the user menu.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method UserMenuItem[] getMenuItems($parentMenuItem = null)
 */
class UserMenu extends TreeMenu
{
    use TUserOptionsByCategory;

    /**
     * editable options grouped by the name of the parent category and the
     * name of their category
     * @var array<string, array<string, UserOption[]>>
     */
    private array $editableOptions = [];

    #[\Override]
    protected function loadCache()
    {
        parent::loadCache();

        $this->menuItems = UserMenuCacheBuilder::getInstance()->getData();
    }

    #[\Override]
    protected function checkMenuItem(ITreeMenuItem $item)
    {
        /** @var UserMenuItem $item */

        if (!parent::checkMenuItem($item)) {
            return false;
        }

        // Hide links to user option categories without accessible options.
        if (\str_starts_with($item->menuItem, 'wcf.user.option.category.')) {
            $categoryName = \substr($item->menuItem, \strlen('wcf.user.option.category.'));
            if (!$this->hasEditableOptions($categoryName)) {
                return false;
            }
        }

        return $item->getProcessor()->isVisible();
    }

    /**
     * Returns `true` if the given option category or one of its child
     * categories contains options that the active user can edit.
     */
    private function hasEditableOptions(string $categoryName): bool
    {
        $category = UserOptionCacheBuilder::getInstance()->getData([], 'categories')[$categoryName] ?? null;
        if ($category === null) {
            return false;
        }

        $parentCategoryName = $category->parentCategoryName;
        if (!isset($this->editableOptions[$parentCategoryName])) {
            $this->editableOptions[$parentCategoryName] = $this->getUserOptionsByCategory(
                $parentCategoryName,
                static fn(UserOption $option) => $option->validateOptions()
                    && $option->validatePermissions()
                    && $option->isEditable()
            );
        }

        return isset($this->editableOptions[$parentCategoryName][$categoryName]);
    }

    /**
     * @return list<array{
     *  category: UserMenuItem,
     *  items: list<string>,
     *  link: string,
     * }>
     * @since 5.5
     */
    public function getUserMenuItems(): array
    {
        $data = [];
        foreach ($this->getMenuItems('') as $category) {
            $link = '';
            $items = [];
            foreach ($this->getMenuItems($category->menuItem) as $item) {
                if (!$link) {
                    $link = $item->getProcessor()->getLink();
                }

                $items[] = $item->getTitle();
            }

            $data[] = [
                'category' => $category,
                'items' => $items,
                'link' => $link,
            ];
        }

        return $data;
    }
}
