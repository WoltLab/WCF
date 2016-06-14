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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Object\Watch
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
	 * @inheritDoc
	 */
	public function resetObject($objectType, $objectID) {
		$this->resetObjects($objectType, [$objectID]);
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
		$conditionsBuilder->add('objectTypeID = ?', [$objectTypeObj->objectTypeID]);
		$conditionsBuilder->add('objectID IN (?)', [$objectIDs]);
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
	public function deleteObjects($objectType, array $objectIDs, array $userIDs = []) {
		// get object type id
		$objectTypeObj = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.user.objectWatch', $objectType);
		
		// delete objects
		$conditionsBuilder = new PreparedStatementConditionBuilder();
		$conditionsBuilder->add('objectTypeID = ?', [$objectTypeObj->objectTypeID]);
		$conditionsBuilder->add('objectID IN (?)', [$objectIDs]);
		if (!empty($userIDs)) $conditionsBuilder->add('userID IN (?)', [$userIDs]);
		
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
	public function updateObject($objectType, $objectID, $notificationEventName, $notificationObjectType, IUserNotificationObject $notificationObject, array $additionalData = []) {
		// get object type id
		$objectTypeObj = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.user.objectWatch', $objectType);
		
		// get subscriber
		$userIDs = $recipientIDs = [];
		$sql = "SELECT		userID, notification
			FROM		wcf".WCF_N."_user_object_watch
			WHERE		objectTypeID = ?
					AND objectID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$objectTypeObj->objectTypeID, $objectID]);
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
