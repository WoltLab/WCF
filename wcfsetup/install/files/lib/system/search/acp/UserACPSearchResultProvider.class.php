<?php
namespace wcf\system\search\acp;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * ACP search result provider implementation for users.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Search\Acp
 */
class UserACPSearchResultProvider implements IACPSearchResultProvider {
	/**
	 * @inheritDoc
	 */
	public function search($query) {
		if (!WCF::getSession()->getPermission('admin.user.canEditUser')) {
			return [];
		}
		
		$results = [];
		
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_user
			WHERE	username LIKE ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$query.'%']);
		
		/** @var User $user */
		while ($user = $statement->fetchObject(User::class)) {
			if (UserGroup::isAccessibleGroup($user->getGroupIDs())) {
				$results[] = new ACPSearchResult($user->username, LinkHandler::getInstance()->getLink('UserEdit', [
					'object' => $user
				]));
			}
		}
		
		return $results;
	}
}
