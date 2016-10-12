<?php
namespace wcf\system\user\notification\object\type;
use wcf\data\comment\Comment;
use wcf\data\comment\CommentList;
use wcf\system\user\notification\object\CommentUserNotificationObject;

/**
 * User notification object type implementation for moderation queue comments.
 * 
 * @author	Mathias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Object\Type
 * @since	3.0
 */
class ModerationQueueCommentUserNotificationObjectType extends AbstractUserNotificationObjectType implements IMultiRecipientCommentUserNotificationObjectType {
	use TMultiRecipientModerationQueueCommentUserNotificationObjectType;
	
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
}
