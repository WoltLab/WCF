<?php
namespace wcf\system\cache\builder;

/**
 * Caches user group options and categories
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class UserGroupOptionCacheBuilder extends OptionCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\OptionCacheBuilder::$optionClassName
	 */
	protected $optionClassName = 'wcf\data\user\group\option\UserGroupOption';
	
	/**
	 * @see	\wcf\system\cache\builder\OptionCacheBuilder::$tableName
	 */
	protected $tableName = 'user_group_option';
	
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		$data = parent::rebuild($parameters);
		
		$usersOnlyPermissions = array();
		foreach ($data['options'] as $option) {
			if ($option->usersOnly) {
				$usersOnlyPermissions[] = $option->optionName;
			}
		}
		
		$data['usersOnlyOptions'] = $usersOnlyPermissions;
		
		return $data;
	}
}
