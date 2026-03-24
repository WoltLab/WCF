<?php

namespace wcf\system\user\notification\object;

use wcf\data\DatabaseObjectDecorator;
use wcf\data\user\follow\UserFollow;

/**
 * Represents a following user as a notification object.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin   UserFollow
 * @extends DatabaseObjectDecorator<UserFollow>
 */
class UserFollowUserNotificationObject extends DatabaseObjectDecorator implements IUserNotificationObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = UserFollow::class;

    #[\Override]
    public function getTitle(): string
    {
        return '';
    }

    #[\Override]
    public function getURL()
    {
        return '';
    }

    #[\Override]
    public function getAuthorID()
    {
        return $this->userID;
    }
}
