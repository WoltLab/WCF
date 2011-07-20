<?php
namespace wcf\data\user\notification\event;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes user notification event-related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.notification
 * @subpackage	data.user.notification.event
 * @category 	Community Framework
 */
class UserNotificationEventAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\user\notification\event\UserNotificationEventEditor';
}
