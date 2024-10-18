<?php

namespace wcf\acp\page;

use wcf\data\user\option\category\UserOptionCategoryList;
use wcf\page\SortablePage;

/**
 * Shows a list of user option categories.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property    UserOptionCategoryList $objectList
 */
class UserOptionCategoryListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.user.option.category.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.user.canManageUserOption'];

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'showOrder';

    /**
     * @inheritDoc
     */
    public $objectListClassName = UserOptionCategoryList::class;

    /**
     * @inheritDoc
     */
    public $validSortFields = ['categoryID', 'categoryName', 'showOrder', 'userOptions'];

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();

        $this->objectList->sqlSelects = "(
            SELECT  COUNT(*)
            FROM    wcf1_user_option
            WHERE   categoryName = user_option_category.categoryName
        ) AS userOptions";
        $this->objectList->getConditionBuilder()->add('user_option_category.parentCategoryName = ?', ['profile']);
    }
}
