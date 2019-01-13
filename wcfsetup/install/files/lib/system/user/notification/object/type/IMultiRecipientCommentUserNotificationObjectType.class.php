<?php
namespace wcf\system\user\notification\object\type;
use wcf\data\comment\Comment;

/**
 * Default interface for comment user notification object types with notifications
 * being sent to multiple recipients.
 * 
 * This interface can also be implemented by user notification object types for
 * comment responses. In this case, there is no distinction between commentResponse
 * and commentResponseOwner event and only a commentResponse event is fired.
 * 
 * Since version 5.2 it is possible to send an `commentResponseOwner` notification 
 * even if you implement this interface. Simple add the interface 
 * `IMultiRecipientCommentResponseOwnerUserNotificationObjectType`, too.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Object\Type
 * @since	3.0
 */
interface IMultiRecipientCommentUserNotificationObjectType {
	/**
	 * Returns the user ids of the notification recipients. If an empty array
	 * is returned, no notifications should be sent.
	 * 
	 * @param	Comment		$comment
	 * @return	integer[]
	 */
	public function getRecipientIDs(Comment $comment);
}
