<?php
namespace wcf\system\cache\builder;
use wcf\data\user\group\option\UserGroupOption;

/**
 * Caches user group options and categories
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 */
class UserGroupOptionCacheBuilder extends OptionCacheBuilder {
	/**
	 * @inheritDoc
	 */
	protected $optionClassName = UserGroupOption::class;
	
	/**
	 * @inheritDoc
	 */
	protected $tableName = 'user_group_option';
	
	/**
	 * @inheritDoc
	 */
	public function rebuild(array $parameters) {
		$data = parent::rebuild($parameters);
		
		$usersOnlyPermissions = [];
		foreach ($data['options'] as $option) {
			if ($option->usersOnly) {
				$usersOnlyPermissions[] = $option->optionName;
			}
		}
		
		$data['usersOnlyOptions'] = $usersOnlyPermissions;
		
		return $data;
	}
}
