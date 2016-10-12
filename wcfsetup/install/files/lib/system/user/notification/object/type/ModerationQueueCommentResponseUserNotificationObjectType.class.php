<?php
namespace wcf\system\user\notification\object\type;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\CommentResponseList;
use wcf\system\user\notification\object\CommentResponseUserNotificationObject;

/**
 * User notification object type implementation for moderation queue comment responses.
 *
 * @author	Mathias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Object\Type
 * @since	3.0
 */
class ModerationQueueCommentResponseUserNotificationObjectType extends AbstractUserNotificationObjectType implements IMultiRecipientCommentUserNotificationObjectType { 
	use TMultiRecipientModerationQueueCommentUserNotificationObjectType;
	
	/**
	 * @inheritDoc
	 */
	protected static $decoratorClassName = CommentResponseUserNotificationObject::class;
	
	/**
	 * @inheritDoc
	 */
	protected static $objectClassName = CommentResponse::class;
	
	/**
	 * @inheritDoc
	 */
	protected static $objectListClassName = CommentResponseList::class;
}
