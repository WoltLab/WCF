<?php
namespace wcf\system\message\embedded\object;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\bbcode\BBCodeParser;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Default interface of embedded object handler.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.message.embedded.object
 * @category	Community Framework
 */
class MessageEmbeddedObjectManager extends SingletonFactory {
	/**
	 * caches message to embedded object assignments
	 * @var	array
	 */
	protected $messageEmbeddedObjects = array();
	
	/**
	 * caches embedded objects
	 * @var	array
	 */
	protected $embeddedObjects = array();
	
	/**
	 * object type of the active message
	 * @var	integer
	 */
	protected $activeMessageObjectTypeID = null;
	
	/**
	 * id of the active message
	 * @var	integer
	 */
	protected $activeMessageID = null;
	
	/**
	 * list of embedded object handlers
	 * @var	array
	 */
	protected $embeddedObjectHandlers = null;
	
	/**
	 * Registers the embedded objects found in given message.
	 * 
	 * @param	string		$messageObjectType
	 * @param	integer		$messageID
	 * @param	string		$message
	 * @return	boolean
	 */
	public function registerObjects($messageObjectType, $messageID, $message) {
		// remove [code] tags
		$message = BBCodeParser::getInstance()->removeCodeTags($message);
		
		// delete existing assignments
		$this->removeObjects($messageObjectType, array($messageID));
		
		// get object type id
		$messageObjectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.message', $messageObjectType);
		
		// prepare statement
		$sql = "INSERT INTO	wcf".WCF_N."_message_embedded_object
					(messageObjectTypeID, messageID, embeddedObjectTypeID, embeddedObjectID)
			VALUES		(?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		// call embedded object handlers
		WCF::getDB()->beginTransaction();
		$returnValue = false;
		foreach ($this->getEmbeddedObjectHandlers() as $handler) {
			$objectIDs = $handler->parseMessage($message);
			if (!empty($objectIDs)) {
				$returnValue = true;
				foreach ($objectIDs as $objectID) {
					$statement->execute(array($messageObjectTypeID, $messageID, $handler->objectTypeID, $objectID));
				}
			}
		}
		WCF::getDB()->commitTransaction();
		
		return $returnValue;
	}
	
	/**
	 * Removes embedded object assigments for given messages.
	 * 
	 * @param	string			$messageObjectType
	 * @param	array<integer>		$messageIDs
	 */
	public function removeObjects($messageObjectType, array $messageIDs) {
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('messageObjectTypeID = ?', array(ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.message', $messageObjectType)));
		$conditionBuilder->add('messageID IN (?)', array($messageIDs));
		
		$sql = "DELETE FROM	wcf".WCF_N."_message_embedded_object
			".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
	}
	
	/**
	 * Loads the embedded objects for given messages.
	 * 
	 * @param	string			$messageObjectType
	 * @param	array<integer>		$messageIDs
	 */
	public function loadObjects($messageObjectType, array $messageIDs) {
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('messageObjectTypeID = ?', array(ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.message', $messageObjectType)));
		$conditionBuilder->add('messageID IN (?)', array($messageIDs));
		
		// get object ids
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_message_embedded_object
			".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		$embeddedObjects = array();
		while ($row = $statement->fetchArray()) {
			if (isset($this->embeddedObjects[$row['embeddedObjectTypeID']][$row['embeddedObjectID']])) {
				// embedded object already loaded
				continue;
			}
			
			// group objects by object type
			if (!isset($embeddedObjects[$row['embeddedObjectTypeID']])) $embeddedObjects[$row['embeddedObjectTypeID']] = array();
			$embeddedObjects[$row['embeddedObjectTypeID']][] = $row['embeddedObjectID'];
			
			// store message to embedded object assignment
			if (!isset($this->messageEmbeddedObjects[$row['messageObjectTypeID']][$row['messageID']][$row['embeddedObjectTypeID']])) {
				$this->messageEmbeddedObjects[$row['messageObjectTypeID']][$row['messageID']][$row['embeddedObjectTypeID']] = array();
			}
			$this->messageEmbeddedObjects[$row['messageObjectTypeID']][$row['messageID']][$row['embeddedObjectTypeID']][] = $row['embeddedObjectID'];
		}
		
		// load objects
		foreach ($embeddedObjects as $embeddedObjectTypeID => $objectIDs) {
			if (!isset($this->embeddedObjects[$embeddedObjectTypeID])) $this->embeddedObjects[$embeddedObjectTypeID] = array();
			foreach ($this->getEmbeddedObjectHandler($embeddedObjectTypeID)->loadObjects(array_unique($objectIDs)) as $objectID => $object) {
				$this->embeddedObjects[$embeddedObjectTypeID][$objectID] = $object;
			}
		}
	}
	
	/**
	 * Sets active message information.
	 * 
	 * @param	string		$messageObjectType
	 * @param	integer		$messageID
	 */
	public function setActiveMessage($messageObjectType, $messageID) {
		$this->activeMessageObjectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.message', $messageObjectType);
		$this->activeMessageID = $messageID;
	}
	
	/**
	 * Returns all embedded objects of a specific type.
	 * 
	 * @param	string		$embeddedObjectType
	 * @return	array
	 */
	public function getObjects($embeddedObjectType) {
		$embeddedObjectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.message.embeddedObject', $embeddedObjectType);
		$returnValue = array();
		if (!empty($this->messageEmbeddedObjects[$this->activeMessageObjectTypeID][$this->activeMessageID][$embeddedObjectTypeID])) {
			foreach ($this->messageEmbeddedObjects[$this->activeMessageObjectTypeID][$this->activeMessageID][$embeddedObjectTypeID] as $embeddedObjectID) {
				if (isset($this->embeddedObjects[$embeddedObjectTypeID][$embeddedObjectID])) {
					$returnValue[] = $this->embeddedObjects[$embeddedObjectTypeID][$embeddedObjectID];
				}
			}
		}
		
		return $returnValue;
	}
	
	/**
	 * Returns a specific embedded object.
	 * 
	 * @param	string		$embeddedObjectType
	 * @param	integer		$objectID
	 * @return	\wcf\data\DatabaseObject
	 */
	public function getObject($embeddedObjectType, $objectID) {
		$embeddedObjectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.message.embeddedObject', $embeddedObjectType);
		if (!empty($this->messageEmbeddedObjects[$this->activeMessageObjectTypeID][$this->activeMessageID][$embeddedObjectTypeID])) {
			foreach ($this->messageEmbeddedObjects[$this->activeMessageObjectTypeID][$this->activeMessageID][$embeddedObjectTypeID] as $embeddedObjectID) {
				if ($embeddedObjectID == $objectID) {
					if (isset($this->embeddedObjects[$embeddedObjectTypeID][$embeddedObjectID])) {
						return $this->embeddedObjects[$embeddedObjectTypeID][$embeddedObjectID];
					}
				}
			}
		}
		
		return null;
	}
	
	/**
	 * Parses a temporary message and loads found embedded objects.
	 * 
	 * @param	string		$message
	 */
	public function parseTemporaryMessage($message) {
		// remove [code] tags
		$message = BBCodeParser::getInstance()->removeCodeTags($message);
		
		// set active message information
		$this->activeMessageObjectTypeID = -1;
		$this->activeMessageID = -1;
		
		// get embedded objects
		foreach ($this->getEmbeddedObjectHandlers() as $handler) {
			$objectIDs = $handler->parseMessage($message);
			if (!empty($objectIDs)) {
				// save assignments
				$this->messageEmbeddedObjects[$this->activeMessageObjectTypeID][$this->activeMessageID][$handler->objectTypeID] = $objectIDs;
				
				// loads objects
				$this->embeddedObjects[$handler->objectTypeID] = $handler->loadObjects($objectIDs);
			}
		}
	}
	
	/**
	 * Returns all embedded object handlers.
	 * 
	 * @return	array
	 */
	protected function getEmbeddedObjectHandlers() {
		if ($this->embeddedObjectHandlers === null) {
			$this->embeddedObjectHandlers = array();
			foreach (ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.message.embeddedObject') as $objectType) {
				$this->embeddedObjectHandlers[$objectType->objectTypeID] = $objectType->getProcessor();
			}
		}
		
		return $this->embeddedObjectHandlers;
	}
	
	/**
	 * Returns a specific embedded object handler.
	 * 
	 * @param	integer		$objectTypeID
	 * @return	object
	 */
	protected function getEmbeddedObjectHandler($objectTypeID) {
		$this->getEmbeddedObjectHandlers();
		
		return $this->embeddedObjectHandlers[$objectTypeID];
	}
}
