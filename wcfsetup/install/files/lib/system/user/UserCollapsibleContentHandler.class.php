<?php
namespace wcf\system\user;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Provides methods for handling collapsible containers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user
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
				$sql = "SELECT	objectID
					FROM	wcf".WCF_N."_collapsible_content
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
				FROM	wcf".WCF_N."_collapsible_content
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
				$sql = "INSERT INTO	wcf".WCF_N."_collapsible_content
							(objectTypeID, objectID, userID)
					VALUES		(?, ?, ?)";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute(array(
					$objectTypeID,
					$objectID,
					WCF::getUser()->userID
				));
			}
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
			$sql = "DELETE FROM	wcf".WCF_N."_collapsible_content
				WHERE		objectTypeID = ?
						AND objectID = ?
						AND userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				$objectTypeID,
				$objectID,
				WCF::getUser()->userID
			));
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
}
