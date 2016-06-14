<?php
namespace wcf\data\edit\history\entry;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\DatabaseObject;

/**
 * Represents an edit history entry
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Edit\History\Entry
 *
 * @property-read	integer		$entryID
 * @property-read	integer		$objectTypeID
 * @property-read	integer		$objectID
 * @property-read	integer|null	$userID
 * @property-read	string		$username
 * @property-read	integer		$time
 * @property-read	integer		$obsoletedAt
 * @property-read	integer|null	$obsoletedByUserID
 * @property-read	string		$message
 * @property-read	string		$editReason
 */
class EditHistoryEntry extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'edit_history_entry';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'entryID';
	
	/**
	 * @inheritDoc
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
