<?php
namespace wcf\system\user\notification\object\type;
use wcf\data\comment\Comment;
use wcf\data\comment\CommentList;
use wcf\system\user\notification\object\CommentUserNotificationObject;

/**
 * Represents a comment notification object type for comments on pages.
 * 
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Object\Type
 * @since       5.2
 */
class PageCommentUserNotificationObjectType extends AbstractUserNotificationObjectType implements IMultiRecipientCommentUserNotificationObjectType, IMultiRecipientCommentResponseOwnerUserNotificationObjectType {
	use TMultiRecipientPageCommentUserNotificationObjectType;
	
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
	public function getCommentOwnerID(Comment $comment) {
		return $comment->userID;
	}
}
