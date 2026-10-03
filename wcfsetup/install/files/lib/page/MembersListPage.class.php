<?php

namespace wcf\page;

use wcf\system\listView\user\MembersListView;

/**
 * Shows members page.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractListViewPage<MembersListView>
 */
class MembersListPage extends AbstractListViewPage
{
    /**
     * @inheritDoc
     */
    public $neededPermissions = ['user.profile.canViewMembersList'];

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_MEMBERS_LIST'];

    /**
     * @var list<string>
     * @deprecated 6.2 No longer in use, but here for backwards compatibility.
     */
    public $validSortFields = ['username', 'registrationDate', 'activityPoints', 'likesReceived', 'lastActivityTime'];

    #[\Override]
    protected function createListView(): MembersListView
    {
        return new MembersListView();
    }
}
