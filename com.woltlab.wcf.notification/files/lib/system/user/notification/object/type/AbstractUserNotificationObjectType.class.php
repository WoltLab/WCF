<?php
namespace wcf\system\user\notification\object\type;
use wcf\data\DatabaseObjectDecorator;

/**
 * Provides default a implementation for user notification object types.
 *
 * @author	Marcel Werk, Oliver Kliebisch
 * @copyright	2001-2011 WoltLab GmbH, Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.notification
 * @subpackage	system.user.notification.event
 * @category 	Community Framework
 */
abstract class AbstractUserNotificationObjectType extends DatabaseObjectDecorator implements IUserNotificationObjectType {
	/**
	 * @see wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\notification\object\type\UserNotificationObjectType';
}
