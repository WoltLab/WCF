<?php
namespace wcf\system\user\notification\object\type;
use wcf\data\user\follow\UserFollow;
use wcf\data\user\follow\UserFollowList;
use wcf\system\user\notification\object\UserFollowUserNotificationObject;

/**
 * Represents a following user as a notification object type.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Object\Type
 */
class UserFollowUserNotificationObjectType extends AbstractUserNotificationObjectType {
	/**
	 * @inheritDoc
	 */
	protected static $decoratorClassName = UserFollowUserNotificationObject::class;
	
	/**
	 * @inheritDoc
	 */
	protected static $objectClassName = UserFollow::class;
	
	/**
	 * @inheritDoc
	 */
	protected static $objectListClassName = UserFollowList::class;
}
