<?php

namespace wcf\system\user\notification\object\type;

use wcf\data\comment\Comment;
use wcf\data\comment\CommentList;
use wcf\system\user\notification\object\CommentUserNotificationObject;
use wcf\system\WCF;

/**
 * Represents a comment notification object type.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserProfileCommentUserNotificationObjectType extends AbstractUserNotificationObjectType implements
    ICommentUserNotificationObjectType
{
    /**
     * @inheritDoc
     */
    protected static $decoratorClassName = CommentUserNotificationObject::class;

    /**
     * @inheritDoc
     */
    protected static $objectClassName = Comment::class;

    /**
     * @inheritDoc
     */
    protected static $objectListClassName = CommentList::class;

    /**
     * @inheritDoc
     */
    public function getOwnerID($objectID)
    {
        $sql = "SELECT  objectID
                FROM    wcf1_comment
                WHERE   commentID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$objectID]);
        $row = $statement->fetchArray();

        return $row ? $row['objectID'] : 0;
    }
}
