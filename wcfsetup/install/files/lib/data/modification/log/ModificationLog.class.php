<?php
namespace wcf\data\modification\log;
use wcf\data\DatabaseObject;

/**
 * Represents a modification log.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Modification\Log
 *
 * @property-read	integer		$logID
 * @property-read	integer		$objectTypeID
 * @property-read	integer		$objectID
 * @property-read	integer|null	$parentObjectID
 * @property-read	integer|null	$userID
 * @property-read	string		$username
 * @property-read	integer		$time
 * @property-read	string		$action
 * @property-read	array		$additionalData
 */
class ModificationLog extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'modification_log';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'logID';
	
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
