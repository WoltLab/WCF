<?php
namespace wcf\system\user\notification\object\type;

/**
 * Represents a comment response notification object type.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.notification.object.type
 * @category	Community Framework
 */
class UserProfileCommentResponseUserNotificationObjectType extends AbstractUserNotificationObjectType {
	/**
	 * @inheritDoc
	 */
	protected static $decoratorClassName = 'wcf\system\user\notification\object\CommentResponseUserNotificationObject';
	
	/**
	 * @inheritDoc
	 */
	protected static $objectClassName = 'wcf\data\comment\response\CommentResponse';
	
	/**
	 * @inheritDoc
	 */
	protected static $objectListClassName = 'wcf\data\comment\response\CommentResponseList';
}
