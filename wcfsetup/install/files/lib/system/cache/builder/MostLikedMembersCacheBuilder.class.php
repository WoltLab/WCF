<?php
namespace wcf\system\cache\builder;
use wcf\data\user\UserList;
use wcf\system\WCF;

/**
 * Caches a list of the most liked members.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.like
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class MostLikedMembersCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	wcf\system\cache\builder\AbstractCacheBuilder::$maxLifetime
	 */
	protected $maxLifetime = 600;
	
	/**
	 * @see	wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	protected function rebuild(array $parameters) {
		$userProfileList = new UserList();
		$userProfileList->getConditionBuilder()->add('user_table.likesReceived > 0');
		$userProfileList->sqlOrderBy = 'user_table.likesReceived DESC';
		$userProfileList->sqlLimit = 5;
		$userProfileList->readObjectIDs();
		
		return $userProfileList->getObjectIDs();
	}
}
