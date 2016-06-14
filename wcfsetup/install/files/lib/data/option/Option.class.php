<?php
namespace wcf\data\option;
use wcf\data\DatabaseObject;
use wcf\data\TDatabaseObjectOptions;
use wcf\data\TDatabaseObjectPermissions;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents an option.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Option
 *
 * @property-read	integer		$optionID
 * @property-read	integer		$packageID
 * @property-read	string		$optionName
 * @property-read	string		$categoryName
 * @property-read	string		$optionType
 * @property-read	string		$optionValue
 * @property-read	string		$validationPattern
 * @property-read	string		$selectOptions
 * @property-read	string		$enableOptions
 * @property-read	integer		$showOrder
 * @property-read	integer		$hidden
 * @property-read	string		$permissions
 * @property-read	string		$options
 * @property-read	integer		$supportI18n
 * @property-read	integer		$requireI18n
 * @property-read	array		$additionalData
 */
class Option extends DatabaseObject {
	use TDatabaseObjectOptions;
	use TDatabaseObjectPermissions;
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'option';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'optionID';
	
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
		
		// unserialize additional data
		$this->data['additionalData'] = (empty($data['additionalData']) ? [] : @unserialize($data['additionalData']));
	}
	
	/**
	 * Returns a list of options.
	 * 
	 * @return	Option[]
	 */
	public static function getOptions() {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_option";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		
		$options = [];
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
		
		return [
			'disableOptions' => $disableOptions,
			'enableOptions' => $enableOptions
		];
	}
	
	/**
	 * Returns a list of the available options.
	 * 
	 * @return	array
	 */
	public function parseSelectOptions() {
		$result = [];
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
		$result = [];
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
	 * @inheritDoc
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
