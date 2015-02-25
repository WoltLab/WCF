<?php
namespace wcf\data\user\notification;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a user notification.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.notification
 * @category	Community Framework
 */
class UserNotification extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'user_notification';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'notificationID';
	
	/**
	 * @see	\wcf\data\IStorableObject::__get()
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
	 * @see	\wcf\data\DatabaseObject::handleData()
	 */
	protected function handleData($data) {
		parent::handleData($data);
		
		$this->data['additionalData'] = @unserialize($this->data['additionalData']);
		if (!is_array($this->data['additionalData'])) {
			$this->data['additionalData'] = array();
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
		$statement->execute(array($packageID, $eventID, $objectID));
		$row = $statement->fetchArray();
		if ($row !== false) return new UserNotification(null, $row);
		
		return null;
	}
}
