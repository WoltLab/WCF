<?php
namespace wcf\data\user\notification;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a user notification.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.notification
 * @category	Community Framework
 *
 * @property-read	integer		$notificationID
 * @property-read	integer		$packageID		deprecated
 * @property-read	integer		$eventID
 * @property-read	integer		$objectID
 * @property-read	integer		$baseObjectID
 * @property-read	string		$eventHash
 * @property-read	integer|null	$authorID
 * @property-read	integer		$timesTriggered
 * @property-read	integer		$guestTimesTriggered
 * @property-read	integer		$userID
 * @property-read	integer		$time
 * @property-read	integer		$mailNotified
 * @property-read	integer		$confirmTime
 * @property-read	array		$additionalData
 */
class UserNotification extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'user_notification';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'notificationID';
	
	/**
	 * @inheritDoc
	 */
	public function __get($name) {
		$value = parent::__get($name);
		
		// treat additional data as data variables if it is an array
		if ($value === null && isset($this->data['additionalData'][$name])) {
			$value = $this->data['additionalData'][$name];
		}
		
		return $value;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function handleData($data) {
		parent::handleData($data);
		
		$this->data['additionalData'] = @unserialize($this->data['additionalData']);
		if (!is_array($this->data['additionalData'])) {
			$this->data['additionalData'] = [];
		}
	}
	
	/**
	 * Returns an existing notification.
	 * 
	 * @param	integer		$packageID
	 * @param	integer		$eventID
	 * @param	integer		$objectID
	 * @return	\wcf\data\user\notification\UserNotification
	 */
	public static function getNotification($packageID, $eventID, $objectID) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_user_notification
			WHERE	packageID = ?
				AND eventID = ?
				AND objectID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$packageID, $eventID, $objectID]);
		$row = $statement->fetchArray();
		if ($row !== false) return new UserNotification(null, $row);
		
		return null;
	}
}
