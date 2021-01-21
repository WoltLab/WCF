<?php

namespace wcf\system\user\notification\object\type;

use wcf\data\comment\Comment;

/**
 * Default interface for comment user notification object types with notifications
 * being sent to multiple recipients and has a `commentResponseOwner` event.
 *
 * This interface is only required, if you use the interface `IMultiRecipientCommentUserNotificationObjectType`.
 * If you use not this interface, the `commentResponseOwner` event is fired by default.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\User\Notification\Object\Type
 * @since   5.2
 */
interface IMultiRecipientCommentResponseOwnerUserNotificationObjectType
{
    /**
     * Returns the user id of the comment owner.
     *
     * @param   Comment     $comment
     * @return  int
     */
    public function getCommentOwnerID(Comment $comment);
}
