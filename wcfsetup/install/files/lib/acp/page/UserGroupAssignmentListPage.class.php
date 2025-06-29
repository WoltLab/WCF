<?php

namespace wcf\acp\page;

use wcf\page\AbstractGridViewPage;
use wcf\system\gridView\admin\UserGroupAssignmentGridView;
use wcf\system\WCF;

/**
 * Lists the available automatic user group assignments.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractGridViewPage<UserGroupAssignmentGridView>
 */
final class UserGroupAssignmentListPage extends AbstractGridViewPage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.group.assignment';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.user.canManageGroupAssignment'];

    #[\Override]
    protected function createGridView(): UserGroupAssignmentGridView
    {
        return new UserGroupAssignmentGridView();
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'hasLegacyObjects' => $this->hasLegacyObjects(),
        ]);
    }

    private function hasLegacyObjects(): bool
    {
        $sql = "SELECT COUNT(*) AS count
                FROM   wcf1_user_group_assignment
                WHERE  isLegacy = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([1]);

        return $statement->fetchColumn() > 0;
    }
}
