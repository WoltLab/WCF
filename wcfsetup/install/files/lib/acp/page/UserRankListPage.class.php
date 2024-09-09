<?php

namespace wcf\acp\page;

use wcf\data\user\rank\I18nUserRankList;
use wcf\page\SortablePage;
use wcf\system\request\LinkHandler;
use wcf\system\view\grid\UserRankGridView;
use wcf\system\WCF;

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
            LEFT JOIN   wcf' . WCF_N . '_user_group user_group
            ON          user_group.groupID = user_rank.groupID';
    }

    public function assignVariables()
    {
        parent::assignVariables();

        $view = new UserRankGridView(isset($_GET['pageNo']) ? \intval($_GET['pageNo']) : 1);
        $view->setBaseUrl(LinkHandler::getInstance()->getControllerLink(self::class));

        WCF::getTPL()->assign([
            'view' => $view,
        ]);
    }
}
