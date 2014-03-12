<?php
namespace wcf\data\user\notification\event;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\WCF;

/**
 * Executes user notification event-related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.notification.event
 * @category	Community Framework
 */
class UserNotificationEventAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::create();
	 */
	public function create() {
		$event = parent::create();
		
		if ($event->preset) {
			$sql = "INSERT INTO	wcf".WCF_N."_user_notification_event_to_user
						(userID, eventID)
				SELECT		userID, ".$event->eventID."
				FROM		wcf".WCF_N."_user";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
		}
		
		return $event;
	}
}
