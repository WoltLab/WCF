<?php
namespace wcf\data\user\notification\event;
use wcf\data\DatabaseObjectEditor;
use wcf\system\WCF;

/**
 * Provides functions to edit user notification events.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.notification.event
 * @category	Community Framework
 */
class UserNotificationEventEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\notification\event\UserNotificationEvent';
	
	/**
	 * @see	\wcf\data\DatabaseObjectEditor::create();
	 */
	public static function create(array $parameters = array()) {
		$event = parent::create($parameters);
		
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
