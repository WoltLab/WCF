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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Option
 * 
 * @method	UserOption	getDecoratedObject()
 * @mixin	UserOption
 */
class UserOptionEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = UserOption::class;
	
	/**
	 * @inheritDoc
	 */
	public static function create(array $parameters = []) {
		$userOption = parent::create($parameters);
		
		// alter the table "wcf".WCF_N."_user_option_value" with this new option
		WCF::getDB()->getEditor()->addColumn('wcf'.WCF_N.'_user_option_value', 'userOption'.$userOption->optionID, self::getColumnDefinition($parameters['optionType']));
		
		// add the default value to this column
		if (isset($parameters['defaultValue']) && $parameters['defaultValue'] !== null) {
			$sql = "UPDATE	wcf".WCF_N."_user_option_value
				SET	userOption".$userOption->optionID." = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$parameters['defaultValue']]);
		}
		
		return $userOption;
	}
	
	/**
	 * @inheritDoc
	 */
	public function update(array $parameters = []) {
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
	 * @inheritDoc
	 */
	public function delete() {
		$sql = "DELETE FROM	wcf".WCF_N."_user_option
			WHERE		optionID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->optionID]);
		
		WCF::getDB()->getEditor()->dropColumn('wcf'.WCF_N.'_user_option_value', 'userOption'.$this->optionID);
	}
	
	/**
	 * @inheritDoc
	 */
	public static function deleteAll(array $objectIDs = []) {
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
		$statement->execute([$value, $this->optionID]);
	}
	
	/**
	 * Determines the needed sql statement to modify column definitions.
	 * 
	 * @param	string		$optionType
	 * @return	array		column definition
	 */
	public static function getColumnDefinition($optionType) {
		$column = [
			'autoIncrement' => false,
			'key' => false,
			'notNull' => false,
			'type' => 'text'
		];
		
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
	 * @inheritDoc
	 */
	public static function resetCache() {
		UserOptionCacheBuilder::getInstance()->reset();
	}
}
