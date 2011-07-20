<?php
namespace wcf\data\user\notification\object\type;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit user notification object types.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.notification
 * @subpackage	data.user.notification.object.type
 * @category 	Community Framework
 */
class UserNotificationObjectTypeEditor extends DatabaseObjectEditor {
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\notification\object\type\UserNotificationObjectType';
}
