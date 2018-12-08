<?php
namespace wcf\system\cache\builder;
use wcf\system\WCF;

/**
 * Caches a list of users that visited the website in last 24 hours.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 */
class WhoWasOnlineCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	protected $maxLifetime = 600;
	
	/**
	 * @inheritDoc
	 */
	protected function rebuild(array $parameters) {
		$userIDs = [];
		$sql = "(SELECT userID FROM wcf".WCF_N."_user WHERE lastActivityTime > ?)
			UNION
			(SELECT userID FROM wcf".WCF_N."_session WHERE userID IS NOT NULL AND lastActivityTime > ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([TIME_NOW - 86400, TIME_NOW - USER_ONLINE_TIMEOUT]);
		while ($userID = $statement->fetchColumn()) {
			$userIDs[] = $userID;
		}
		
		return $userIDs;
	}
}
