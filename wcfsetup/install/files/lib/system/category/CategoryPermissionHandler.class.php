<?php

namespace wcf\system\category;

use wcf\data\category\Category;
use wcf\data\user\User;
use wcf\system\cache\builder\CategoryACLOptionCacheBuilder;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles the category permissions.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class CategoryPermissionHandler extends SingletonFactory
{
    /**
     * cached category acl options
     * @var array<int, array{
     *  group: array<int, array<string, int>>,
     *  user: array<int, array<string, int>>,
     * }>
     */
    protected $categoryPermissions = [];

    /**
     * Returns the acl options for the given category and for the given user.
     * If no user is given, the active user is used.
     *
     * @return array<string, int>
     */
    public function getPermissions(Category $category, ?User $user = null)
    {
        if ($user === null) {
            $user = WCF::getUser();
        }

        $permissions = [];
        if (isset($this->categoryPermissions[$category->categoryID])) {
            foreach ($user->getGroupIDs() as $groupID) {
                if (isset($this->categoryPermissions[$category->categoryID]['group'][$groupID])) {
                    foreach ($this->categoryPermissions[$category->categoryID]['group'][$groupID] as $optionName => $optionValue) {
                        if (isset($permissions[$optionName])) {
                            $permissions[$optionName] = $permissions[$optionName] || $optionValue;
                        } else {
                            $permissions[$optionName] = $optionValue;
                        }
                    }
                }
            }

            if (isset($this->categoryPermissions[$category->categoryID]['user'][$user->userID])) {
                foreach ($this->categoryPermissions[$category->categoryID]['user'][$user->userID] as $optionName => $optionValue) {
                    $permissions[$optionName] = $optionValue;
                }
            }
        }

        return $permissions;
    }

    #[\Override]
    protected function init()
    {
        $this->categoryPermissions = CategoryACLOptionCacheBuilder::getInstance()->getData();
    }

    /**
     * Resets the category permission cache.
     *
     * @return void
     */
    public function resetCache()
    {
        CategoryACLOptionCacheBuilder::getInstance()->reset();
    }
}
