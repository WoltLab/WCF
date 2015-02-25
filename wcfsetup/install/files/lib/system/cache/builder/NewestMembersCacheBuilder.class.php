<?php
namespace wcf\system\cache\builder;
use wcf\data\user\UserList;

/**
 * Caches a list of the newest members.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class NewestMembersCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::$maxLifetime
	 */
	protected $maxLifetime = 300;
	
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	protected function rebuild(array $parameters) {
		$userProfileList = new UserList();
		$userProfileList->sqlOrderBy = 'user_table.registrationDate DESC';
		$userProfileList->sqlLimit = 5;
		$userProfileList->readObjectIDs();
		
		return $userProfileList->getObjectIDs();
	}
}
