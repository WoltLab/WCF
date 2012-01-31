<?php
namespace wcf\system\cache\builder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\WCF;
use wcf\util\ClassUtil;
use wcf\util\StringUtil;

/**
 * Caches the merged group options of a group combination.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category 	Community Framework
 */
class UserGroupPermissionCacheBuilder implements ICacheBuilder {
	/**
	 * list of used group option type objects
	 * @var	array<wcf\system\option\group\IGroupOptionType>
	 */
	protected $typeObjects = array();
	
	/**
	 * @see wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) {
		list($cache, $packageID, $groupIDs) = explode('-', $cacheResource['cache']);
		$data = array();
		
		// get all options and filter options with low priority
		if ($packageID == 0) {
			// during the installation of the package wcf
			$sql = "SELECT		optionName, optionID 
				FROM		wcf".WCF_N."_user_group_option
				WHERE 		packageID IS NULL";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
		}
		else {
			$sql = "SELECT		optionName, optionID 
				FROM		wcf".WCF_N."_user_group_option option_table
				LEFT JOIN	wcf".WCF_N."_package_dependency package_dependency
				ON		(package_dependency.dependency = option_table.packageID)
				WHERE 		package_dependency.packageID = ?
				ORDER BY	package_dependency.priority ASC";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($packageID));
		}
		
		$options = array();
		while ($row = $statement->fetchArray()) {
			$options[$row['optionName']] = $row['optionID'];
		}
		
		if (count($options) > 0) {
			// get needed options
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("option_value.groupID IN (?)", array(explode(',', $groupIDs)));
			$conditions->add("option_value.optionID IN (?)", array($options));
			
			$sql = "SELECT		option_table.optionName, option_table.optionType, option_value.optionValue
				FROM		wcf".WCF_N."_user_group_option_value option_value
				LEFT JOIN	wcf".WCF_N."_user_group_option option_table
				ON		(option_table.optionID = option_value.optionID)
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			while ($row = $statement->fetchArray()) {
				if (!isset($data[$row['optionName']])) {
					$data[$row['optionName']] = array('type' => $row['optionType'], 'values' => array());
				}
				
				$data[$row['optionName']]['values'][] = $row['optionValue'];
			}
			
			// merge values
			foreach ($data as $optionName => $option) {
				if (count($option['values']) == 1) {
					$result = $option['values'][0];
				}
				else {
					$typeObj = $this->getTypeObject($option['type']);
					$result = $typeObj->merge($option['values']);
				}
				
				// unset false values
				if ($result === false) {
					unset($data[$optionName]);
				}
				else {
					$data[$optionName] = $result;
				}
			}
		}
		
		$data['groupIDs'] = $groupIDs;
		return $data;
	}
	
	/**
	 * Returns an object of the requested group option type.
	 * 
	 * @param	string			$type
	 * @return	wcf\system\option\user\group\IUserGroupOptionType
	 */
	protected function getTypeObject($type) {
		if (!isset($this->typeObjects[$type])) {
			$className = 'wcf\system\option\user\group\\'.StringUtil::firstCharToUpperCase($type).'UserGroupOptionType';
			
			// validate class
			if (!class_exists($className)) {
				throw new SystemException("unable to find class '".$className."'");
			}
			if (!ClassUtil::isInstanceOf($className, 'wcf\system\option\user\group\IUserGroupOptionType')) {
				throw new SystemException("'".$className."' should implement wcf\system\option\user\group\IUserGroupOptionType");
			}
			
			// create instance
			$this->typeObjects[$type] = new $className();
		}
		
		return $this->typeObjects[$type];
	}
}
