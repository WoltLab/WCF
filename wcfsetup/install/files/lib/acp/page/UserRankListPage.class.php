<?php

namespace wcf\acp\page;

use wcf\data\user\rank\I18nUserRankList;
use wcf\page\SortablePage;

/**
 * Lists available user ranks.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property    I18nUserRankList $objectList
 */
class UserRankListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.user.rank.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.user.rank.canManageRank'];

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_USER_RANK'];

    /**
     * @inheritDoc
     */
    public $objectListClassName = I18nUserRankList::class;

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'rankTitleI18n';

    /**
     * @inheritDoc
     */
    public $validSortFields = ['rankID', 'groupID', 'requiredPoints', 'rankTitleI18n', 'rankImage', 'requiredGender'];

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();

        $this->objectList->sqlSelects .= (!empty($this->objectList->sqlSelects) ? ', ' : '') . 'user_group.groupName';
        $this->objectList->sqlJoins .= '
            LEFT JOIN   wcf1_user_group user_group
            ON          user_group.groupID = user_rank.groupID';
    }
}
