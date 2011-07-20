<?php
namespace wcf\data\user\notification\type;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes user notification type-related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.notification
 * @subpackage	data.user.notification.type
 * @category 	Community Framework
 */
class UserNotificationTypeAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\user\notification\type\UserNotificationTypeEditor';
}
