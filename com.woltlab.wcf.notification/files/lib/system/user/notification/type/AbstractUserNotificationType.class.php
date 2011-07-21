<?php
namespace wcf\system\user\notification\type;
use wcf\data\DatabaseObjectDecorator;

/**
 * Provides default a implementation for user notification types.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.notification
 * @subpackage	system.user.notification.type
 * @category 	Community Framework
 */
abstract class AbstractUserNotificationType extends DatabaseObjectDecorator implements IUserNotificationType {
	/**
	 * @see wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\notification\type\UserNotificationType';
}
