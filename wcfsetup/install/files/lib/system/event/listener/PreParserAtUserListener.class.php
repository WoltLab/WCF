<?php
namespace wcf\system\event\listener;
use wcf\data\bbcode\BBCode;
use wcf\data\user\UserList;
use wcf\system\event\IEventListener;
use wcf\system\request\LinkHandler;
use wcf\system\Callback;
use wcf\system\Regex;
use wcf\util\StringUtil;

/**
 * Parses @user mentions.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user
 * @subpackage	system.event.listener
 * @category	Community Framework
 */
class PreParserAtUserListener implements IEventListener {
	/**
	 * @see wcf\system\event\IEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (!$eventObj->text) return;
		
		// check if needed url BBCode is allowed
		if ($eventObj->allowedBBCodes !== null && !BBCode::isAllowedBBCode('url', $eventObj->allowedBBCodes)) {
			return;
		}
		
		static $userRegex = null;
		if ($userRegex === null) {
			$userRegex = new Regex("(?<=^|\s)@([^',\s][^,\s]{2,}|'(?:''|[^'])*')");
		}
		
		$userRegex->match($eventObj->text, true);
		$matches = $userRegex->getMatches();
		
		if (!empty($matches[1])) {
			$usernames = array();
			foreach ($matches[1] as $match) {
				$username = self::getUsername($match);
				if (!in_array($username, $usernames)) $usernames[] = $username; 
			}
			
			if (!empty($usernames)) {
				// fetch users
				$userList = new UserList();
				$userList->getConditionBuilder()->add('user_table.username IN (?)', array($usernames));
				$userList->readObjects();
				$users = array();
				foreach ($userList as $user) {
					$users[StringUtil::toLowerCase($user->username)] = $user; 
				}
				
				$eventObj->text = $userRegex->replace($eventObj->text, new Callback(function ($matches) use ($users) {
					$username = PreParserAtUserListener::getUsername($matches[1]);
					
					if (isset($users[$username])) {
						$link = LinkHandler::getInstance()->getLink('User', array(
							'object' => $users[$username]
						));
						return "[url='".$link."']@".$users[$username]->username.'[/url]';
					}
					
					return $matches[0];
				}));
			}
		}
	}
	
	/**
	 * Returns the username for the given regular expression match.
	 * 
	 * @param	string		$match
	 * @return	string
	 */
	public static function getUsername($match) {
		// remove escaped single quotation mark
		$match = StringUtil::replace("''", "'", $match);
		
		// remove single quotation marks
		if ($match{0} == "'") {
			$match = StringUtil::substring($match, 1, -1);
		}
		
		return StringUtil::toLowerCase($match);
	}
}
