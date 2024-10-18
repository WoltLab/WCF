<?php

namespace wcf\system\box;

use wcf\data\user\online\UsersOnlineList;
use wcf\system\WCF;

/**
 * Box controller for a list of staff members who are currently online.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class StaffOnlineListBoxController extends AbstractDatabaseObjectListBoxController
{
    /**
     * @inheritDoc
     */
    protected static $supportedPositions = ['sidebarLeft', 'sidebarRight'];

    /**
     * @inheritDoc
     */
    protected function getObjectList()
    {
        $objectList = new UsersOnlineList();
        $objectList->getConditionBuilder()->add(
            'session.userID IN (
                SELECT  userID
                FROM    wcf1_user_to_group
                WHERE   groupID IN (
                            SELECT  groupID
                            FROM    wcf1_user_group
                            WHERE   showOnTeamPage = ?
                        )
            )',
            [1]
        );

        return $objectList;
    }

    /**
     * @inheritDoc
     */
    protected function getTemplate()
    {
        return WCF::getTPL()->fetch('boxStaffOnline', 'wcf', ['usersOnlineList' => $this->objectList], true);
    }

    /**
     * @inheritDoc
     */
    public function hasContent()
    {
        if (!MODULE_USERS_ONLINE || !WCF::getSession()->getPermission('user.profile.canViewUsersOnlineList')) {
            return false;
        }

        return parent::hasContent();
    }
}
