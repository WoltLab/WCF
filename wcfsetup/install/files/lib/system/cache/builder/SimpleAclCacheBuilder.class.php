<?php
namespace wcf\system\cache\builder;
use wcf\system\acl\simple\SimpleAclHandler;
use wcf\system\WCF;

/**
 * Caches the simple ACL settings per object type.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 */
class SimpleAclCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	public function rebuild(array $parameters) {
		$data = [];
		
		$objectTypeID = SimpleAclHandler::getInstance()->getObjectTypeID($parameters['objectType']);
		
		$sql = "SELECT  objectID, userID
			FROM    wcf".WCF_N."_acl_simple_to_user
			WHERE   objectTypeID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$objectTypeID]);
		while ($row = $statement->fetchArray()) {
			$objectID = $row['objectID'];
			
			if (!isset($data[$objectID])) {
				$data[$objectID] = [
					'group' => [],
					'user' => []
				];
			}
			
			$data[$objectID]['user'][] = $row['userID'];
		}
		
		$sql = "SELECT  objectID, groupID
			FROM    wcf".WCF_N."_acl_simple_to_group
			WHERE   objectTypeID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$objectTypeID]);
		while ($row = $statement->fetchArray()) {
			$objectID = $row['objectID'];
			
			if (!isset($data[$objectID])) {
				$data[$objectID] = [
					'group' => [],
					'user' => []
				];
			}
			
			$data[$objectID]['group'][] = $row['groupID'];
		}
		
		return $data;
	}
}
