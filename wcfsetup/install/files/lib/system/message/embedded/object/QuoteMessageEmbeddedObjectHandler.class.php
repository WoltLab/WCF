<?php
namespace wcf\system\message\embedded\object;
use wcf\data\user\UserList;
use wcf\data\user\UserProfile;

/**
 * IMessageEmbeddedObjectHandler implementation for quotes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.message.embedded.object
 * @category	Community Framework
 */
class QuoteMessageEmbeddedObjectHandler extends AbstractMessageEmbeddedObjectHandler {
	/**
	 * @see	\wcf\system\message\embedded\object\IMessageEmbeddedObjectHandler::parseMessage()
	 */
	public function parseMessage($message) {
		$usernames = self::getFirstParameters($message, 'quote');
		if (!empty($usernames)) {
			$userList = new UserList();
			$userList->getConditionBuilder()->add("user_table.username IN (?)", array($usernames));
			$userList->readObjectIDs();
			return $userList->getObjectIDs();
		}
		
		return false;
	}
	
	/**
	 * @see	\wcf\system\message\embedded\object\IMessageEmbeddedObjectHandler::loadObjects()
	 */
	public function loadObjects(array $objectIDs) {
		return UserProfile::getUserProfiles($objectIDs);
	}
}
