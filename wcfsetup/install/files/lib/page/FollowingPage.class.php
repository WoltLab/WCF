<?php

namespace wcf\page;

use wcf\data\user\follow\UserFollowingList;
use wcf\system\menu\user\UserMenu;
use wcf\system\WCF;

/**
 * Shows a list with all users the active user is following.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends MultipleLinkPage<UserFollowingList>
 */
class FollowingPage extends MultipleLinkPage
{
    /**
     * @inheritDoc
     */
    public $loginRequired = true;

    /**
     * @inheritDoc
     */
    public $objectListClassName = UserFollowingList::class;

    /**
     * @inheritDoc
     */
    public $sqlOrderBy = 'user_follow.time DESC';

    #[\Override]
    protected function initObjectList()
    {
        parent::initObjectList();

        $this->objectList->getConditionBuilder()->add("user_follow.userID = ?", [WCF::getUser()->userID]);
    }

    #[\Override]
    public function show()
    {
        // set active tab
        UserMenu::getInstance()->setActiveMenuItem('wcf.user.menu.community.following');

        parent::show();
    }
}
