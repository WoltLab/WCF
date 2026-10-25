<?php

namespace wcf\page;

use wcf\system\listView\user\FollowingListView;
use wcf\system\menu\user\UserMenu;
use wcf\system\WCF;

/**
 * Shows a list with all users the active user is following.
 *
 * @author      Alexander Ebert, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractListViewPage<FollowingListView>
 */
class FollowingPage extends AbstractListViewPage
{
    /**
     * @inheritDoc
     */
    public $loginRequired = true;

    #[\Override]
    protected function createListView(): FollowingListView
    {
        return new FollowingListView(WCF::getUser()->userID);
    }

    #[\Override]
    public function show()
    {
        // set active tab
        UserMenu::getInstance()->setActiveMenuItem('wcf.user.menu.community.following');

        parent::show();
    }
}
