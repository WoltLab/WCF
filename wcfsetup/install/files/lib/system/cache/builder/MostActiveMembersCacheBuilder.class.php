<?php
namespace wcf\system\cache\builder;
use wcf\data\user\UserList;

/**
 * Caches a list of the most active members.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class MostActiveMembersCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::$maxLifetime
	 */
	protected $maxLifetime = 600;
	
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	protected function rebuild(array $parameters) {
		$userProfileList = new UserList();
		$userProfileList->getConditionBuilder()->add('user_table.activityPoints > 0');
		$userProfileList->sqlOrderBy = 'user_table.activityPoints DESC';
		$userProfileList->sqlLimit = 5;
		$userProfileList->readObjectIDs();
		
		return $userProfileList->getObjectIDs();
	}
}
