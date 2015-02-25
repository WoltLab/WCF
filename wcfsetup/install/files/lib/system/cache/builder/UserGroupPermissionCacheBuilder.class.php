<?php
namespace wcf\system\cache\builder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\WCF;
use wcf\util\ClassUtil;
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
	 * @var	array<\wcf\system\option\group\IGroupOptionType>
	 */
	protected $typeObjects = array();
	
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		$data = array();
		
		// get all options
		$sql = "SELECT	optionName, optionID
			FROM	wcf".WCF_N."_user_group_option";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		
		$options = array();
		while ($row = $statement->fetchArray()) {
			$options[$row['optionName']] = $row['optionID'];
		}
		
		if (!empty($options)) {
			// get needed options
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("option_value.groupID IN (?)", array($parameters));
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
					$result = array_shift($option['values']);
					foreach ($option['values'] as $value) {
						$newValue = $typeObj->merge($result, $value);
						if ($newValue !== null) {
							$result = $newValue;
						}
					}
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
		
		$data['groupIDs'] = $parameters;
		return $data;
	}
	
	/**
	 * Returns an object of the requested group option type.
	 * 
	 * @param	string			$type
	 * @return	\wcf\system\option\user\group\IUserGroupOptionType
	 */
	protected function getTypeObject($type) {
		if (!isset($this->typeObjects[$type])) {
			$className = 'wcf\system\option\user\group\\'.StringUtil::firstCharToUpperCase($type).'UserGroupOptionType';
			
			// validate class
			if (!class_exists($className)) {
				throw new SystemException("unable to find class '".$className."'");
			}
			if (!ClassUtil::isInstanceOf($className, 'wcf\system\option\user\group\IUserGroupOptionType')) {
				throw new SystemException("'".$className."' does not implement 'wcf\system\option\user\group\IUserGroupOptionType'");
			}
			
			// create instance
			$this->typeObjects[$type] = new $className();
		}
		
		return $this->typeObjects[$type];
	}
}
