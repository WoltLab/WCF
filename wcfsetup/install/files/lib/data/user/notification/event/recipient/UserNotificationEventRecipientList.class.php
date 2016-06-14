<?php
namespace wcf\data\user\notification\event\recipient;
use wcf\data\user\UserList;

/**
 * Extends the user list to provide special functions for handling recipients of user notifications.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Notification\Event\Recipient
 */
class UserNotificationEventRecipientList extends UserList {
	/**
	 * @inheritDoc
	 */
	public function __construct() {
		$this->sqlJoins = "LEFT JOIN wcf".WCF_N."_user user_table ON (user_table.userID = event_to_user.userID)";
		$this->sqlSelects = 'user_table.*';
		
		parent::__construct();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getDatabaseTableName() {
		return 'wcf'.WCF_N.'_user_notification_event_to_user';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getDatabaseTableAlias() {
		return 'event_to_user';
	}
}
