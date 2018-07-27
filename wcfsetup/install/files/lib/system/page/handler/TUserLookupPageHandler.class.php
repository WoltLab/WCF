<?php
namespace wcf\system\page\handler;
use wcf\data\user\UserProfileList;
use wcf\system\cache\runtime\UserRuntimeCache;

/**
 * Provides the `isValid` and `lookup` methods for looking up users. 
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Page\Handler
 * @since	3.0
 */
trait TUserLookupPageHandler {
	/**
	 * Returns true if provided object id exists and is valid.
	 *
	 * @param	integer		$objectID	page object id
	 * @return	boolean		true if object id is valid
	 * @see	ILookupPageHandler::isValid()
	 */
	public function isValid($objectID) {
		return UserRuntimeCache::getInstance()->getObject($objectID) !== null;
	}
	
	/**
	 * Performs a search for pages using a query string, returning an array containing
	 * an `objectID => title` relation.
	 *
	 * @param	string		$searchString	search string
	 * @return	string[]
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
				'link' => $this->getLink($user->userID),
				'objectID' => $user->userID,
				'title' => $user->username
			];
		}
		
		return $results;
	}
}
