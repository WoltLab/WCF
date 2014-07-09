<?php
namespace wcf\system\edit;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Manages the edit history.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.search
 * @category	Community Framework
 */
class EditHistoryManager extends SingletonFactory {
	/**
	 * list of available object types
	 * @var	array
	 */
	protected $availableObjectTypes = array();
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		// get available object types
		$this->availableObjectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.edit.historySavingObject');
	}
	
	/**
	 * Returns the id of the object type with the given name.
	 * 
	 * @param	string		$objectType
	 * @return	integer
	 */
	public function getObjectTypeID($objectType) {
		if (!isset($this->availableObjectTypes[$objectType])) {
			throw new SystemException("unknown object type '".$objectType."'");
		}
		
		return $this->availableObjectTypes[$objectType]->objectTypeID;
	}
	
	/**
	 * Adds a new entry.
	 * 
	 * @param	string		$objectType
	 * @param	integer		$objectID
	 * @param	string		$message
	 * @param	integer		$time
	 * @param	integer		$userID
	 * @param	string		$username
	 * @param	string		$editReason
	 */
	public function add($objectType, $objectID, $message, $time, $userID, $username, $editReason) {
		// save new entry
		$sql = "INSERT INTO	wcf".WCF_N."_edit_history_entry
					(objectTypeID, objectID, message, time, insertionTime, userID, username, editReason)
			VALUES		(?, ?, ?, ?, ?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->getObjectTypeID($objectType), $objectID, $message, $time, TIME_NOW, $userID, $username, $editReason));
	}
	
	/**
	 * Deletes edit history entries.
	 * 
	 * @param	string		$objectType
	 * @param	array<integer>	$objectIDs
	 */
	public function delete($objectType, array $objectIDs) {
		$objectTypeID = $this->getObjectTypeID($objectType);
		
		$sql = "DELETE FROM	wcf".WCF_N."_edit_history_entry
			WHERE		objectTypeID = ?
				AND	objectID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		WCF::getDB()->beginTransaction();
		foreach ($objectIDs as $objectID) {
			$statement->execute(array($objectTypeID, $objectID));
		}
		WCF::getDB()->commitTransaction();
	}
}
