<?php
namespace wcf\data\modification\log;
use wcf\data\DatabaseObject;

/**
 * Represents a modification log.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.modification.log
 * @category	Community Framework
 */
class ModificationLog extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'modification_log';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'logID';
	
	/**
	 * @see	\wcf\data\IStorableObject::__get()
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
	 * @see	\wcf\data\DatabaseObject::handleData()
	 */
	protected function handleData($data) {
		parent::handleData($data);
		
		$this->data['additionalData'] = @unserialize($this->data['additionalData']);
		if (!is_array($this->data['additionalData'])) {
			$this->data['additionalData'] = array();
		}
	}
}
