<?php

namespace wcf\system\menu\user;

use wcf\data\user\menu\item\UserMenuItem;
use wcf\system\cache\builder\UserMenuCacheBuilder;
use wcf\system\menu\ITreeMenuItem;
use wcf\system\menu\TreeMenu;
use wcf\system\option\user\UserOptionHandler;
use wcf\system\WCF;

/**
 * Builds the user menu.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Menu\User
 * 
 * @method UserMenuItem[] getMenuItems()
 */
class UserMenu extends TreeMenu
{
    /**
     * user option handler for the `settings` category
     * @var UserOptionHandler
     */
    protected $optionHandler;

    /**
     * @inheritDoc
     */
    protected function loadCache()
    {
        parent::loadCache();

        $this->menuItems = UserMenuCacheBuilder::getInstance()->getData();
        $this->optionHandler = new UserOptionHandler(false, '', 'settings');
        $this->optionHandler->setUser(WCF::getUser());
    }

    /**
     * @inheritDoc
     */
    protected function checkMenuItem(ITreeMenuItem $item)
    {
        /** @var UserMenuItem $item */

        if (!parent::checkMenuItem($item)) {
            return false;
        }

        // Hide links to user option categories without accessible options.
        if (\strpos($item->menuItem, 'wcf.user.option.category.') === 0) {
            $categoryName = \str_replace('wcf.user.option.category.', '', $item->menuItem);
            if (!$this->optionHandler->countCategoryOptions($categoryName)) {
                return false;
            }
        }

        return $item->getProcessor()->isVisible();
    }

    /**
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
