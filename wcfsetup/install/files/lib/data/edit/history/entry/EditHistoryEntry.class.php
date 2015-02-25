<?php
namespace wcf\data\edit\history\entry;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\DatabaseObject;

/**
 * Represents an edit history entry
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.edit.history.entry
 * @category	Community Framework
 */
class EditHistoryEntry extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'edit_history_entry';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'entryID';
	
	/**
	 * @see	\wcf\data\edit\history\entry\EntryHistoryEntry::getObject()
	 */
	protected $object = null;
	
	/**
	 * Returns the message text of the history entry.
	 * 
	 * @return	string
	 */
	public function getMessage() {
		return $this->message;
	}
	
	/**
	 * Returns the corresponding IHistorySavingObject
	 * 
	 * @return	\wcf\system\edit\IHistorySavingObject
	 */
	public function getObject() {
		if ($this->object === null) {
			$objectType = ObjectTypeCache::getInstance()->getObjectType($this->objectTypeID);
			$processor = $objectType->getProcessor();
			
			$this->object = $processor->getObjectByID($this->objectID);
		}
		
		return $this->object;
	}
}
