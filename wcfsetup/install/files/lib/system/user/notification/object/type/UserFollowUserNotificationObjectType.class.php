<?php
namespace wcf\system\user\notification\object\type;

/**
 * Represents a following user as a notification object type.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.notification.object.type
 * @category	Community Framework
 */
class UserFollowUserNotificationObjectType extends AbstractUserNotificationObjectType {
	/**
	 * @inheritDoc
	 */
	protected static $decoratorClassName = 'wcf\system\user\notification\object\UserFollowUserNotificationObject';
	
	/**
	 * @inheritDoc
	 */
	protected static $objectClassName = 'wcf\data\user\follow\UserFollow';
	
	/**
	 * @inheritDoc
	 */
	protected static $objectListClassName = 'wcf\data\user\follow\UserFollowList';
}
