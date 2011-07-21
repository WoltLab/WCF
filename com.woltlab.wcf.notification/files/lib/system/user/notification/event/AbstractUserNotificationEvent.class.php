<?php
namespace wcf\system\user\notification\event;
use wcf\data\DatabaseObjectDecorator;

/**
 * Provides default a implementation for user notification events.
 *
 * @author	Marcel Werk, Oliver Kliebisch
 * @copyright	2001-2011 WoltLab GmbH, Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.notification
 * @subpackage	system.user.notification.event
 * @category 	Community Framework
 */
abstract class AbstractUserNotificationEvent extends DatabaseObjectDecorator implements IUserNotificationEvent {
	/**
	 * @see wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\notification\event\UserNotificationEvent';
}
