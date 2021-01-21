<?php

namespace wcf\system\user\notification\object;

use wcf\data\comment\Comment;
use wcf\data\DatabaseObjectDecorator;

/**
 * Notification object for comments.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\User\Notification\Object
 *
 * @method  Comment     getDecoratedObject()
 * @mixin   Comment
 */
class CommentUserNotificationObject extends DatabaseObjectDecorator implements IUserNotificationObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Comment::class;

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
