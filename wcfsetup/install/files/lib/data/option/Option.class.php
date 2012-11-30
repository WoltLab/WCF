<?php
namespace wcf\data\option;
use wcf\data\DatabaseObject;
use wcf\data\package\Package;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents an option.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.option
 * @category	Community Framework
 */
class Option extends DatabaseObject {
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'option';
	
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'optionID';
	
	// equals to an empty bitmask, NOT a valid bit!
	const VISIBILITY_NONE = 0;
	
	const VISIBILITY_OWNER = 1;
	const VISIBILITY_ADMINISTRATOR = 2;
	const VISIBILITY_REGISTERED = 4;
	const VISIBILITY_GUEST = 8;
	
	// equals to VISIBILITY_GUEST, NOT a valid bit!
	const VISIBILITY_ALL = 15;
	
	/**
	 * @see	wcf\data\DatabaseObject::handleData()
	 */
	protected function handleData($data) {
		parent::handleData($data);
		
		// unserialize additional data
		$this->data['additionalData'] = (empty($data['additionalData']) ? array() : @unserialize($data['additionalData']));
	}
	
	/**
	 * Returns a list of options.
	 * 
	 * @param	integer		$packageID
	 * @return	array<wcf\data\option\Option>
	 */
	public static function getOptions($packageID = PACKAGE_ID) {
		$sql = "SELECT		option_table.*,
					package.package, package.isApplication,
					parent_package.package AS parentPackage,
					parent_package.isApplication AS parentPackageIsApplication
			FROM		wcf".WCF_N."_package_dependency package_dependency,
					wcf".WCF_N."_option option_table
			LEFT JOIN	wcf".WCF_N."_package package
			ON		(package.packageID = option_table.packageID)
			LEFT JOIN	wcf".WCF_N."_package parent_package
			ON		(parent_package.packageID = package.parentPackageID)
			WHERE		package_dependency.dependency = option_table.packageID
					AND package_dependency.packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($packageID));
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
					$disableOptions .= "'".StringUtil::substring($item, 1)."' ";
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
			if (StringUtil::indexOf($option, ':') !== false) {
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
				if (StringUtil::indexOf($option, ':') !== false) {
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
	 * Returns true, if option is visible
	 * 
	 * @param	boolean		$overrideVisibility
	 * @return	boolean
	 */
	public function isVisible($overrideVisibility = false) {
		return !$this->hidden;
	}
	
	/**
	 * @see	wcf\data\IStorableObject::getDatabaseTableAlias()
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
		$prefix = '';
		if ($this->parentPackage) {
			if ($this->parentPackageIsApplication && $this->parentPackage != 'com.woltlab.wcf') {
				$prefix = Package::getAbbreviation($this->parentPackage) . '_';
			}
		}
		else if ($this->package) {
			if ($this->isApplication && $this->package != 'com.woltlab.wcf') {
				$prefix = Package::getAbbreviation($this->package) . '_';
			}
		}
		
		return strtoupper($prefix.$this->optionName);
	}
}
