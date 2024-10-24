<?php

namespace wcf\acp\page;

use wcf\data\user\group\I18nUserGroupList;
use wcf\page\SortablePage;
use wcf\system\WCF;

/**
 * Shows a list of all user groups.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property    I18nUserGroupList $objectList
 */
class UserGroupListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.group.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.user.canEditGroup', 'admin.user.canDeleteGroup'];

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'groupNameI18n';

    /**
     * @inheritDoc
     */
    public $validSortFields = ['groupID', 'groupNameI18n', 'groupType', 'members', 'priority'];

    /**
     * @inheritDoc
     */
    public $objectListClassName = I18nUserGroupList::class;

    /**
     * indicates if a group has just been deleted
     * @var int
     */
    public $deletedGroups = 0;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        // detect group deletion
        if (isset($_REQUEST['deletedGroups'])) {
            $this->deletedGroups = \intval($_REQUEST['deletedGroups']);
        }
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();

        if (!empty($this->objectList->sqlSelects)) {
            $this->objectList->sqlSelects .= ',';
        }
        $this->objectList->sqlSelects .= "(
            SELECT  COUNT(*)
            FROM    wcf1_user_to_group
            WHERE   groupID = user_group.groupID
        ) AS members";
    }

    /**
     * @inheritDoc
     */
    protected function readObjects()
    {
        $this->sqlOrderBy = (($this->sortField != 'members' && $this->sortField != 'groupNameI18n') ? 'user_group.' : '') . $this->sortField . " " . $this->sortOrder;

        parent::readObjects();
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'deletedGroups' => $this->deletedGroups,
        ]);
    }
}
