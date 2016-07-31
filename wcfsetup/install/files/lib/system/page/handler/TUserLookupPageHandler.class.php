<?php
namespace wcf\system\page\handler;
use wcf\data\user\UserProfileList;

/**
 * Provides the `lookup` method for looking up users. 
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Page\Handler
 * @since	3.0
 */
trait TUserLookupPageHandler {
	/**
	 * @see	ILookupPageHandler::lookup()
	 */
	public function lookup($searchString) {
		$userList = new UserProfileList();
		$userList->getConditionBuilder()->add('user_table.username LIKE ?', ['%' . $searchString . '%']);
		$userList->readObjects();
		
		$results = [];
		foreach ($userList as $user) {
			$results[] = [
				'image' => $user->getAvatar()->getImageTag(48),
				'link' => $user->getLink(),
				'objectID' => $user->userID,
				'title' => $user->username
			];
		}
		
		return $results;
	}
}
