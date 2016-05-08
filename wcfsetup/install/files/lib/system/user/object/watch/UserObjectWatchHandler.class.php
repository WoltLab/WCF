<?php
namespace wcf\system\user\object\watch;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\user\notification\object\IUserNotificationObject;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles watched objects.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.object.watch
 * @category	Community Framework
 */
class UserObjectWatchHandler extends SingletonFactory {
	/**
	 * Returns the id of the given object type.
	 * 
	 * @param	string		$objectTypeName
	 * @return	integer
	 * @throws	SystemException
	 */
	public function getObjectTypeID($objectTypeName) {
		$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.user.objectWatch', $objectTypeName);
		if ($objectType === null) {
			throw new SystemException("unknown object type '".$objectTypeName."'");
		}
		
		return $objectType->objectTypeID;
	}
	
	/**
	 * @see	\wcf\system\user\object\watch\UserObjectWatchHandler::resetObjects();
	 */
	public function resetObject($objectType, $objectID) {
		$this->resetObjects($objectType, array($objectID));
	}
	
	/**
	 * Resets the object watch cache for all subscriber of the given object.
	 * 
	 * @param	string		$objectType
	 * @param	integer[]	$objectIDs
	 */
	public function resetObjects($objectType, array $objectIDs) {
		// get object type id
		$objectTypeObj = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.user.objectWatch', $objectType);
		
		// get subscriber
		$conditionsBuilder = new PreparedStatementConditionBuilder();
		$conditionsBuilder->add('objectTypeID = ?', array($objectTypeObj->objectTypeID));
		$conditionsBuilder->add('objectID IN (?)', array($objectIDs));
		$sql = "SELECT		userID
			FROM		wcf".WCF_N."_user_object_watch
			".$conditionsBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionsBuilder->getParameters());
		$userIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
		
		if (!empty($userIDs)) {
			// reset user storage
			$objectTypeObj->getProcessor()->resetUserStorage($userIDs);
		}
	}
	
	/**
	 * Deletes the given objects.
	 * 
	 * @param	string		$objectType
	 * @param	integer[]	$objectIDs
	 * @param	integer[]	$userIDs
	 */
	public function deleteObjects($objectType, array $objectIDs, array $userIDs = array()) {
		// get object type id
		$objectTypeObj = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.user.objectWatch', $objectType);
		
		// delete objects
		$conditionsBuilder = new PreparedStatementConditionBuilder();
		$conditionsBuilder->add('objectTypeID = ?', array($objectTypeObj->objectTypeID));
		$conditionsBuilder->add('objectID IN (?)', array($objectIDs));
		if (!empty($userIDs)) $conditionsBuilder->add('userID IN (?)', array($userIDs));
		
		$sql = "DELETE FROM	wcf".WCF_N."_user_object_watch
			".$conditionsBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionsBuilder->getParameters());
	}
	
	/**
	 * Updates a watched object for all subscriber.
	 * 
	 * @param	string				$objectType
	 * @param	integer				$objectID
	 * @param	string				$notificationEventName
	 * @param	string				$notificationObjectType
	 * @param	IUserNotificationObject		$notificationObject
	 * @param	array				$additionalData
	 */
	public function updateObject($objectType, $objectID, $notificationEventName, $notificationObjectType, IUserNotificationObject $notificationObject, array $additionalData = array()) {
		// get object type id
		$objectTypeObj = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.user.objectWatch', $objectType);
		
		// get subscriber
		$userIDs = $recipientIDs = array();
		$sql = "SELECT		userID, notification
			FROM		wcf".WCF_N."_user_object_watch
			WHERE		objectTypeID = ?
					AND objectID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($objectTypeObj->objectTypeID, $objectID));
		while ($row = $statement->fetchArray()) {
			$userIDs[] = $row['userID'];
			if ($row['notification'] && $notificationObject->getAuthorID() != $row['userID']) $recipientIDs[] = $row['userID'];
		}
		
		if (!empty($userIDs)) {
			// reset user storage
			$objectTypeObj->getProcessor()->resetUserStorage($userIDs);
			
			if (!empty($recipientIDs)) {
				// create notifications
				UserNotificationHandler::getInstance()->fireEvent($notificationEventName, $notificationObjectType, $notificationObject, $recipientIDs, $additionalData);
			}
		}
	}
}
