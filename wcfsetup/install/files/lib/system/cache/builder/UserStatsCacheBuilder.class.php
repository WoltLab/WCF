<?php
namespace wcf\system\cache\builder;
use wcf\system\WCF;

/**
 * Caches the number of members and the newest member.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class UserStatsCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::$maxLifetime
	 */
	protected $maxLifetime = 600;
	
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	protected function rebuild(array $parameters) {
		$data = array();
		
		// number of members
		$sql = "SELECT	COUNT(*) AS amount
			FROM	wcf".WCF_N."_user";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		$data['members'] = $statement->fetchColumn();
		
		// newest member
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_user
			ORDER BY	userID DESC";
		$statement = WCF::getDB()->prepareStatement($sql, 1);
		$statement->execute();
		$data['newestMember'] = $statement->fetchObject('wcf\data\user\User');
		
		return $data;
	}
}
