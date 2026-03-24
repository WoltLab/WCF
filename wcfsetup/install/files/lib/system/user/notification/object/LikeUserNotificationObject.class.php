<?php

namespace wcf\system\user\notification\object;

use wcf\data\DatabaseObjectDecorator;
use wcf\data\like\Like;

/**
 * User notification object implementation for likes.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin   Like
 * @extends DatabaseObjectDecorator<Like>
 */
class LikeUserNotificationObject extends DatabaseObjectDecorator implements IUserNotificationObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Like::class;

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
