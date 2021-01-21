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
 * @package WoltLabSuite\Core\System\User\Notification\Object
 *
 * @method  Like    getDecoratedObject()
 * @mixin   Like
 */
class LikeUserNotificationObject extends DatabaseObjectDecorator implements IUserNotificationObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Like::class;

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getURL()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getAuthorID()
    {
        return $this->userID;
    }
}
