<?php
namespace wcf\data\user\notification\object\type;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes user notification object type-related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.notification
 * @subpackage	data.user.notification.object.type
 * @category 	Community Framework
 */
class UserNotificationObjectTypeAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\user\notification\object\type\UserNotificationObjectTypeEditor';
}
