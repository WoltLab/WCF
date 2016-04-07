<?php
namespace wcf\system\cache\builder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\option\user\group\IUserGroupOptionType;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Caches the merged user group options for a certain user group combination.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class UserGroupPermissionCacheBuilder extends AbstractCacheBuilder {
	/**
	 * list of used group option type objects
	 * @var	IUserGroupOptionType[]
	 */
	protected $typeObjects = [];
	
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		$data = [];
		
		// get option values
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("option_value.groupID IN (?)", [$parameters]);
		
		$sql = "SELECT		option_table.optionName, option_table.optionType, option_value.optionValue
			FROM		wcf".WCF_N."_user_group_option_value option_value
			LEFT JOIN	wcf".WCF_N."_user_group_option option_table
			ON		(option_table.optionID = option_value.optionID)
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		while ($row = $statement->fetchArray()) {
			if (!isset($data[$row['optionName']])) {
				$data[$row['optionName']] = ['type' => $row['optionType'], 'values' => []];
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
				$result = array_shift($option['values']);
				foreach ($option['values'] as $value) {
					$newValue = $typeObj->merge($result, $value);
					if ($newValue !== null) {
						$result = $newValue;
					}
				}
			}
			
			// handle special value 'Never' for boolean options
			if ($option['type'] === 'boolean' && $result == -1) {
				$result = 0;
			}
			
			// unset false values
			if ($result === false) {
				unset($data[$optionName]);
			}
			else {
				$data[$optionName] = $result;
			}
		}
		
		$data['groupIDs'] = $parameters;
		return $data;
	}
	
	/**
	 * Returns an object of the requested group option type.
	 * 
	 * @param	string		$type
	 * @return	\wcf\system\option\user\group\IUserGroupOptionType
	 * @throws	SystemException
	 */
	protected function getTypeObject($type) {
		if (!isset($this->typeObjects[$type])) {
			$className = 'wcf\system\option\user\group\\'.StringUtil::firstCharToUpperCase($type).'UserGroupOptionType';
			
			// validate class
			if (!class_exists($className)) {
				throw new SystemException("unable to find class '".$className."'");
			}
			if (!is_subclass_of($className, 'wcf\system\option\user\group\IUserGroupOptionType')) {
				throw new SystemException("'".$className."' does not implement 'wcf\system\option\user\group\IUserGroupOptionType'");
			}
			
			// create instance
			$this->typeObjects[$type] = new $className();
		}
		
		return $this->typeObjects[$type];
	}
}
