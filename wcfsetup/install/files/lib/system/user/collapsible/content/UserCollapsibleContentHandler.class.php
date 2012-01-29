<?php
namespace wcf\system\user\collapsible\content;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Provides methods for handling collapsible containers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.collapsible.content
 * @category 	Community Framework
 */
class UserCollapsibleContentHandler extends SingletonFactory {
	/**
	 * object type cache
	 * @var	array<array>
	 */
	protected $cache = null;
	
	/**
	 * list of collapsed object ids per object type id
	 * @var	array<array>
	 */
	protected $collapsedContent = array();
	
	/**
	 * @see	wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$this->cache = array(
			'objectTypes' => array(),
			'objectTypeIDs' => array()
		);
		
		$objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.collapsibleContent');
		foreach ($objectTypes as $objectType) {
			$this->cache['objectTypes'][$objectType->objectTypeID] = $objectType;
			$this->cache['objectTypeIDs'][$objectType->objectType] = $objectType->objectTypeID;
		}
	}
	
	/**
	 * Returns the object type id based upon specified object type name. Returns
	 * null, if object type is unknown.
	 * 
	 * @param	string		$objectType
	 * @return	integer
	 */
	public function getObjectTypeID($objectType) {
		if (isset($this->cache['objectTypeIDs'][$objectType])) {
			return $this->cache['objectTypeIDs'][$objectType];
		}
		
		return null;
	}
	
	/**
	 * Returns a list of object ids being collapsed by current user.
	 * 
	 * @param	integer		$objectTypeID
	 * @return	array<integer>
	 */
	public function getCollapsedContent($objectTypeID) {
		if (!isset($this->collapsedContent[$objectTypeID])) {
			$this->collapsedContent[$objectTypeID] = array();
			
			if (WCF::getUser()->userID) {
				// get data from storage
				UserStorageHandler::getInstance()->loadStorage(array(WCF::getUser()->userID));
						
				// get ids
				$data = UserStorageHandler::getInstance()->getStorage(array(WCF::getUser()->userID), 'collapsedContent-'.$objectTypeID);
					
				// cache does not exist or is outdated
				if ($data[WCF::getUser()->userID] === null) {
					$sql = "SELECT	objectID
						FROM	wcf".WCF_N."_user_collapsible_content
						WHERE	objectTypeID = ?
							AND userID = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute(array(
						$objectTypeID,
						WCF::getUser()->userID
					));
					while ($row = $statement->fetchArray()) {
						$this->collapsedContent[$objectTypeID][] = $row['objectID'];
					}
					
					// update storage data
					UserStorageHandler::getInstance()->update(WCF::getUser()->userID, 'collapsedContent-'.$objectTypeID, serialize($this->collapsedContent[$objectTypeID]), 1);
				}
				else {
					$this->collapsedContent[$objectTypeID] = @unserialize($data[WCF::getUser()->userID]);
				}
			}
			else {
				$collapsedContent = WCF::getSession()->getVar('collapsedContent');
				if ($collapsedContent !== null && is_array($collapsedContent)) {
					if (isset($collapsedContent[$objectTypeID])) {
						$this->collapsedContent[$objectTypeID] = $collapsedContent[$objectTypeID];
					}
				}
			}
		}
		
		return $this->collapsedContent[$objectTypeID];
	}
	
	/**
	 * Marks content as collapsed.
	 * 
	 * @param	integer		$objectTypeID
	 * @param	integer		$objectID
	 */
	public function markAsCollapsed($objectTypeID, $objectID) {
		if (WCF::getUser()->userID) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_user_collapsible_content
				WHERE	objectTypeID = ?
					AND objectID = ?
					AND userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				$objectTypeID,
				$objectID,
				WCF::getUser()->userID
			));
			$row = $statement->fetchArray();
			
			if (!$row) {
				$sql = "INSERT INTO	wcf".WCF_N."_user_collapsible_content
							(objectTypeID, objectID, userID)
					VALUES		(?, ?, ?)";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute(array(
					$objectTypeID,
					$objectID,
					WCF::getUser()->userID
				));
			}
			
			// reset storage
			UserStorageHandler::getInstance()->reset(array(WCF::getUser()->userID), 'collapsedContent-'.$objectTypeID, 1);
		}
		else {
			$collapsedContent = WCF::getSession()->getVar('collapsedContent');
			if ($collapsedContent === null || !is_array($collapsedContent)) {
				$collapsedContent = array();
			}
			
			if (!in_array($objectID, $collapsedContent)) {
				$collapsedContent[$objectTypeID] = array();
			}
			
			$collapsedContent[$objectTypeID][] = $objectID;
			WCF::getSession()->register('collapsedContent', $collapsedContent);
		}
	}
	
	/**
	 * Marks content as opened, thus removing the collapsed marking.
	 * 
	 * @param	integer		$objectTypeID
	 * @param	integer		$objectID
	 */
	public function markAsOpened($objectTypeID, $objectID) {
		if (WCF::getUser()->userID) {
			$sql = "DELETE FROM	wcf".WCF_N."_user_collapsible_content
				WHERE		objectTypeID = ?
						AND objectID = ?
						AND userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				$objectTypeID,
				$objectID,
				WCF::getUser()->userID
			));
			
			// reset storage
			UserStorageHandler::getInstance()->reset(array(WCF::getUser()->userID), 'collapsedContent-'.$objectTypeID, 1);
		}
		else {
			$collapsedContent = WCF::getSession()->getVar('collapsedContent');
			if ($collapsedContent === null || !is_array($collapsedContent)) {
				$collapsedContent = array();
			}
			
			if (isset($collapsedContent[$objectTypeID])) {
				foreach ($collapsedContent[$objectTypeID] as $index => $collapsedObjectID) {
					if ($collapsedObjectID == $objectID) {
						unset($collapsedContent[$objectTypeID][$index]);
					}
				}
			}
			
			WCF::getSession()->register('collapsedContent', $collapsedContent);
		}
	}
	
	/**
	 * Deletes all saved states for a specific object type.
	 * 
	 * @param	integer		$objectTypeID
	 */
	public function reset($objectTypeID) {
		if (WCF::getUser()->userID) {
			$sql = "DELETE FROM	wcf".WCF_N."_user_collapsible_content
				WHERE		objectTypeID = ?
						AND userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				$objectTypeID,
				WCF::getUser()->userID
			));
			
			// reset storage
			UserStorageHandler::getInstance()->reset(array(WCF::getUser()->userID), 'collapsedContent-'.$objectTypeID, 1);
		}
		else {
			$collapsedContent = WCF::getSession()->getVar('collapsedContent');
			if ($collapsedContent === null || !is_array($collapsedContent)) {
				$collapsedContent = array();
			}
			
			if (isset($collapsedContent[$objectTypeID])) {
				unset($collapsedContent[$objectTypeID]);
			}
			
			WCF::getSession()->register('collapsedContent', $collapsedContent);
		}
	}
}
