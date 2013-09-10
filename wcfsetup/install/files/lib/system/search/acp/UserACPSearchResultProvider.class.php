<?php
namespace wcf\system\search\acp;
use wcf\data\user\group\UserGroup;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * ACP search result provider implementation for users.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.search.acp
 * @category	Community Framework
 */
class UserACPSearchResultProvider implements IACPSearchResultProvider {
	/**
	 * @see	wcf\system\search\acp\IACPSearchResultProvider::search()
	 */
	public function search($query) {
		if (!WCF::getSession()->getPermission('admin.user.canEditUser')) {
			return array();
		}
		
		$results = array();
		
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_user
			WHERE	username LIKE ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($query.'%'));
		
		while ($user = $statement->fetchObject('wcf\data\user\User')) {
			if (UserGroup::isAccessibleGroup($user->getGroupIDs())) {
				$results[] = new ACPSearchResult($user->username, LinkHandler::getInstance()->getLink('UserEdit', array(
					'object' => $user
				)));
			}
		}
		
		return $results;
	}
}
