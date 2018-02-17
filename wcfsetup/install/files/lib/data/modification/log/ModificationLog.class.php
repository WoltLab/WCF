<?php
namespace wcf\data\modification\log;
use wcf\data\DatabaseObject;

/**
 * Represents a modification log entry.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Modification\Log
 * 
 * @property-read	integer		$logID			unique id of the modification log entry
 * @property-read	integer		$objectTypeID		id of the `com.woltlab.wcf.modifiableContent` object type
 * @property-read	integer		$objectID		id of the object of the object type with id `$objectTypeID` to which the modification log entry belongs to
 * @property-read	integer|null	$parentObjectID		id of the object's parent object to which the modification log entry belongs to or `null` if no such parent object exists or is logged
 * @property-read	integer|null	$userID			id of the user who caused the modification log entry or `null` if the user does not exist anymore or if the modification log entry has been caused by a guest
 * @property-read	string		$username		name of the user or guest who caused the modification log entry
 * @property-read	integer		$time			timestamp at which the modification log entry has been created
 * @property-read	string		$action			name of the modification action that is logged
 * @property-read	array		$additionalData		array with additional data of the modification log entry
 */
class ModificationLog extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	public function __get($name) {
		$value = parent::__get($name);
		
		// treat additional data as data variables if it is an array
		if ($value === null) {
			if (is_array($this->data['additionalData']) && isset($this->data['additionalData'][$name])) {
				$value = $this->data['additionalData'][$name];
			}
		}
		
		return $value;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function handleData($data) {
		parent::handleData($data);
		
		$this->data['additionalData'] = @unserialize($this->data['additionalData']);
		if (!is_array($this->data['additionalData'])) {
			$this->data['additionalData'] = [];
		}
	}
}
