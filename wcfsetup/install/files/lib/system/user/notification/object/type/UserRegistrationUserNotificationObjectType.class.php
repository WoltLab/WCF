<?php
namespace wcf\system\user\notification\object\type;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\user\notification\object\UserRegistrationUserNotificationObject;

/**
 * Represents a new user registration as a notification object type.
 *
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Object\Type
 * @since       5.2
 */
class UserRegistrationUserNotificationObjectType extends AbstractUserNotificationObjectType {
	/**
	 * @inheritDoc
	 */
	protected static $decoratorClassName = UserRegistrationUserNotificationObject::class;
	
	/**
	 * @inheritDoc
	 */
	protected static $objectClassName = User::class;
	
	/**
	 * @inheritDoc
	 */
	protected static $objectListClassName = UserList::class;
}
