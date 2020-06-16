<?php
namespace wcf\data\edit\history\entry;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\DatabaseObject;

/**
 * Represents an edit history entry.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Edit\History\Entry
 * 
 * @property-read	integer		$entryID		unique id of the edit history entry
 * @property-read	integer		$objectTypeID		id of the `com.woltlab.wcf.edit.historySavingObject` object type
 * @property-read	integer		$objectID		id of the edited object of the object type with id `$objectTypeID`
 * @property-read	integer|null	$userID			id of the user who has created the previous version of the object or `null` if the user does not exist anymore or if the previous version has been created by a guest
 * @property-read	string		$username		name of the user who has created the previous version of the object
 * @property-read	integer		$time			timestamp at which the original version has been created
 * @property-read	integer		$obsoletedAt		timestamp at which the edited version has been created and time used for clean up
 * @property-read	integer|null	$obsoletedByUserID	id of the user who has created this version of the object
 * @property-read	string		$message		message of the edited object prior to the edit
 * @property-read	string		$editReason		reason for the edit
 */
class EditHistoryEntry extends DatabaseObject {
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
