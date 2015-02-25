<?php
namespace wcf\data\user\option;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\UserOptionCacheBuilder;
use wcf\system\WCF;

/**
 * Provides functions to edit user options.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.option
 * @category	Community Framework
 */
class UserOptionEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\option\UserOption';
	
	/**
	 * @see	\wcf\data\IEditableObject::create()
	 */
	public static function create(array $parameters = array()) {
		$userOption = parent::create($parameters);
		
		// alter the table "wcf".WCF_N."_user_option_value" with this new option
		WCF::getDB()->getEditor()->addColumn('wcf'.WCF_N.'_user_option_value', 'userOption'.$userOption->optionID, self::getColumnDefinition($parameters['optionType']));
		
		// add the default value to this column
		if (isset($parameters['defaultValue']) && $parameters['defaultValue'] !== null) {
			$sql = "UPDATE	wcf".WCF_N."_user_option_value
				SET	userOption".$userOption->optionID." = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($parameters['defaultValue']));
		}
		
		return $userOption;
	}
	
	/**
	 * @see	\wcf\data\IEditableObject::update()
	 */
	public function update(array $parameters = array()) {
		parent::update($parameters);
		
		// alter the table "wcf".WCF_N."_user_option_value" with this new option
		if (isset($parameters['optionType']) && $parameters['optionType'] != $this->optionType) {
			WCF::getDB()->getEditor()->alterColumn(
				'wcf'.WCF_N.'_user_option_value',
				'userOption'.$this->optionID,
				'userOption'.$this->optionID,
				self::getColumnDefinition($parameters['optionType'])
			);
		}
	}
	
	/**
	 * @see	\wcf\data\IEditableObject::delete()
	 */
	public function delete() {
		$sql = "DELETE FROM	wcf".WCF_N."_user_option
			WHERE		optionID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->optionID));
		
		WCF::getDB()->getEditor()->dropColumn('wcf'.WCF_N.'_user_option_value', 'userOption'.$this->optionID);
	}
	
	/**
	 * @see	\wcf\data\IEditableObject::deleteAll()
	 */
	public static function deleteAll(array $objectIDs = array()) {
		$returnValue = parent::deleteAll($objectIDs);
		
		foreach ($objectIDs as $objectID) {
			WCF::getDB()->getEditor()->dropColumn('wcf'.WCF_N.'_user_option_value', 'userOption'.$objectID);
		}
		
		return $returnValue;
	}
	
	/**
	 * Disables this option.
	 */
	public function disable() {
		$this->enable(false);
	}
	
	/**
	 * Enables this option.
	 * 
	 * @param	boolean		$enable
	 */
	public function enable($enable = true) {
		$value = intval(!$enable);
		
		$sql = "UPDATE	wcf".WCF_N."_user_option
			SET	isDisabled = ?
			WHERE	optionID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($value, $this->optionID));
	}
	
	/**
	 * Determines the needed sql statement to modify column definitions.
	 * 
	 * @param	string		$optionType
	 * @return	array		column definition
	 */
	public static function getColumnDefinition($optionType) {
		$column = array(
			'autoIncrement' => false,
			'key' => false,
			'notNull' => false,
			'type' => 'text'
		);
		
		switch ($optionType) {
			case 'boolean':
				$column['notNull'] = true;
				$column['default'] = 0;
				$column['length'] = 1;
				$column['type'] = 'tinyint';
			break;
			
			case 'integer':
				$column['notNull'] = true;
				$column['default'] = 0;
				$column['length'] = 10;
				$column['type'] = 'int';
			break;
			
			case 'float':
				$column['notNull'] = true;
				$column['default'] = 0.0;
				$column['type'] = 'float';
			break;
			
			case 'textarea':
				$column['type'] = 'mediumtext';
			break;
			
			case 'birthday':
			case 'date':
				$column['notNull'] = true;
				$column['default'] = "'0000-00-00'";
				$column['length'] = 10;
				$column['type'] = 'char';
			break;
		}
		
		return $column;
	}
	
	/**
	 * @see	\wcf\data\IEditableCachedObject::resetCache()
	 */
	public static function resetCache() {
		UserOptionCacheBuilder::getInstance()->reset();
	}
}
