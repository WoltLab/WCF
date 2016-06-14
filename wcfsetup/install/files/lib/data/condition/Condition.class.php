<?php
namespace wcf\data\condition;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\DatabaseObject;

/**
 * Represents a condition.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Condition
 *
 * @property-read	integer		$conditionID
 * @property-read	integer		$objectTypeID
 * @property-read	integer		$objectID
 * @property-read	array		$conditionData
 */
class Condition extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'conditionID';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'condition';
	
	/**
	 * @inheritDoc
	 */
	public function __get($name) {
		$value = parent::__get($name);
		
		// treat condition data as data variables if it is an array
		if ($value === null && is_array($this->data['conditionData']) && isset($this->data['conditionData'][$name])) {
			$value = $this->data['conditionData'][$name];
		}
		
		return $value;
	}
	
	/**
	 * Returns the condition object type of the condition.
	 * 
	 * @return	\wcf\data\object\type\ObjectType
	 */
	public function getObjectType() {
		return ObjectTypeCache::getInstance()->getObjectType($this->objectTypeID);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function handleData($data) {
		parent::handleData($data);
		
		// handle condition data
		$this->data['conditionData'] = @unserialize($data['conditionData']);
		if (!is_array($this->data['conditionData'])) {
			$this->data['conditionData'] = [];
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getDatabaseTableAlias() {
		return 'condition_table';
	}
}
