<?php

namespace wcf\page;

use wcf\system\listView\user\IgnoredUserListView;
use wcf\system\menu\user\UserMenu;

/**
 * Shows a list with all users the active user ignores.
 *
 * @author      Alexander Ebert, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractListViewPage<IgnoredUserListView>
 */
class IgnoredUsersPage extends AbstractListViewPage
{
    /**
     * @inheritDoc
     */
    public $loginRequired = true;

    #[\Override]
    protected function createListView(): IgnoredUserListView
    {
        return new IgnoredUserListView();
    }

    #[\Override]
    public function show()
    {
        // set active tab
        UserMenu::getInstance()->setActiveMenuItem('wcf.user.menu.community.ignoredUsers');

        parent::show();
    }
}
