<?php
namespace wcf\system\message\embedded\object;
use wcf\data\user\UserList;
use wcf\system\cache\runtime\UserProfileRuntimeCache;

/**
 * IMessageEmbeddedObjectHandler implementation for quotes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.message.embedded.object
 * @category	Community Framework
 */
class QuoteMessageEmbeddedObjectHandler extends AbstractMessageEmbeddedObjectHandler {
	/**
	 * @inheritDoc
	 */
	public function parseMessage($message) {
		$usernames = self::getFirstParameters($message, 'quote');
		if (!empty($usernames)) {
			$userList = new UserList();
			$userList->getConditionBuilder()->add("user_table.username IN (?)", [$usernames]);
			$userList->readObjectIDs();
			return $userList->getObjectIDs();
		}
		
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function loadObjects(array $objectIDs) {
		return UserProfileRuntimeCache::getInstance()->getObjects($objectIDs);
	}
}
