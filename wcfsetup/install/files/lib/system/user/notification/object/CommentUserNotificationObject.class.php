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
 *
 * @mixin   Comment
 * @extends DatabaseObjectDecorator<Comment>
 */
class CommentUserNotificationObject extends DatabaseObjectDecorator implements IUserNotificationObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Comment::class;

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
