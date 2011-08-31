<?php
namespace wcf\data\option;
use wcf\data\DatabaseObject;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents an option.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.option
 * @category 	Community Framework
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
	
	/**
	 * @see wcf\data\DatabaseObject::handleData()
	 */
	protected function handleData($data) {
		parent::handleData($data);
		
		// unserialize additional data
		$this->data['additionalData'] = (empty($data['additionalData']) ? array() : @unserialize($data['additionalData']));
	}
	
	/**
	 * Returns a list of options.
	 * TODO: move to optionlist
	 *
	 * @param	integer		$packageID
	 * @return	array
	 */
	public static function getOptions($packageID = PACKAGE_ID) {
		$sql = "SELECT		optionName, optionID
			FROM		wcf".WCF_N."_option option_table
			LEFT JOIN	wcf".WCF_N."_package_dependency package_dependency
				ON 	(package_dependency.dependency = option_table.packageID)
			WHERE		package_dependency.packageID = ?
			ORDER BY	package_dependency.priority ASC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($packageID));
		
		$optionIDs = array();
		while ($row = $statement->fetchArray()) {
			$optionIDs[$row['optionName']] = $row['optionID'];
		}
		
		$options = array();
		if (count($optionIDs)) {
			// get needed options
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("optionID IN (?)", array($optionIDs));
			
			$sql = "SELECT		optionName, optionValue, optionType
				FROM		wcf".WCF_N."_option
				".$conditions."
				ORDER BY	optionName";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			while ($row = $statement->fetchArray()) {
				$options[StringUtil::toUpperCase($row['optionName'])] = $row;
			}
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
}
