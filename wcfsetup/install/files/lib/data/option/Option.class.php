<?php
namespace wcf\data\option;
use wcf\data\DatabaseObject;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents an option.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.option
 * @category	Community Framework
 */
class Option extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'option';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'optionID';
	
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
		
		// unserialize additional data
		$this->data['additionalData'] = (empty($data['additionalData']) ? array() : @unserialize($data['additionalData']));
	}
	
	/**
	 * Returns a list of options.
	 * 
	 * @return	array<\wcf\data\option\Option>
	 */
	public static function getOptions() {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_option";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		
		$options = array();
		while ($row = $statement->fetchArray()) {
			$option = new Option(null, $row);
			$options[$option->getConstantName()] = $option;
		}
		
		return $options;
	}
	
	/**
	 * Parses enableOptions.
	 * 
	 * @param	string		$optionData
	 * @return	array
	 */
	public static function parseEnableOptions($optionData) {
		$disableOptions = $enableOptions = '';
		
		if (!empty($optionData)) {
			$options = explode(',', $optionData);
			
			foreach ($options as $item) {
				if ($item{0} == '!') {
					if (!empty($disableOptions)) $disableOptions .= ',';
					$disableOptions .= "'".mb_substr($item, 1)."' ";
				}
				else {
					if (!empty($enableOptions)) $enableOptions .= ',';
					$enableOptions .= "'".$item."' ";
				}
			}
		}
		
		return array(
			'disableOptions' => $disableOptions,
			'enableOptions' => $enableOptions
		);
	}
	
	/**
	 * Returns a list of the available options.
	 * 
	 * @return	array
	 */
	public function parseSelectOptions() {
		$result = array();
		$options = explode("\n", StringUtil::trim(StringUtil::unifyNewlines($this->selectOptions)));
		foreach ($options as $option) {
			$key = $value = $option;
			if (mb_strpos($option, ':') !== false) {
				$optionData = explode(':', $option);
				$key = array_shift($optionData);
				$value = implode(':', $optionData);
			}
			
			$result[$key] = $value;
		}
		
		return $result;
	}
	
	/**
	 * Returns a list of the enable options.
	 * 
	 * @return	array
	 */
	public function parseMultipleEnableOptions() {
		$result = array();
		if (!empty($this->enableOptions)) {
			$options = explode("\n", StringUtil::trim(StringUtil::unifyNewlines($this->enableOptions)));
			$key = -1;
			foreach ($options as $option) {
				if (mb_strpos($option, ':') !== false) {
					$optionData = explode(':', $option);
					$key = array_shift($optionData);
					$value = implode(':', $optionData);
				}
				else {
					$key++;
					$value = $option;
				}
				
				$result[$key] = $value;
			}
		}
		
		return $result;
	}
	
	/**
	 * Returns true if option is visible
	 * 
	 * @return	boolean
	 */
	public function isVisible() {
		return !$this->hidden;
	}
	
	/**
	 * @see	\wcf\data\IStorableObject::getDatabaseTableAlias()
	 */
	public static function getDatabaseTableAlias() {
		return 'option_table';
	}
	
	/**
	 * Returns the constant name.
	 * 
	 * @return	string
	 */
	public function getConstantName() {
		return strtoupper($this->optionName);
	}
	
	/**
	 * Allows modifications of select options.
	 * 
	 * @param	string		$selectOptions
	 */
	public function modifySelectOptions($selectOptions) {
		$this->data['selectOptions'] = $selectOptions;
	}
	
	/**
	 * Allows modifications of enable options.
	 * 
	 * @param	string		$enableOptions
	 */
	public function modifyEnableOptions($enableOptions) {
		$this->data['enableOptions'] = $enableOptions;
	}
	
	/**
	 * Allows modifications of hidden option.
	 * 
	 * @param	string		$hiddenOption
	 */
	public function modifyHiddenOption($hiddenOption) {
		$this->data['hidden'] = $hiddenOption;
	}
}
