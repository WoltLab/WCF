<?php
namespace wcf\system\event\listener;
use wcf\data\bbcode\BBCode;
use wcf\data\user\UserList;
use wcf\system\event\IEventListener;
use wcf\system\request\LinkHandler;
use wcf\system\Callback;
use wcf\system\Regex;
use wcf\util\StringStack;
use wcf\util\StringUtil;

/**
 * Parses @user mentions.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
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
		
		// cache quotes
		// @see	wcf\system\bbcode\BBCodeParser::buildTagArray()
		$pattern = '~\[(?:/(?:quote)|(?:quote)
			(?:=
				(?:\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|[^,\]]*)
				(?:,(?:\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|[^,\]]*))*
			)?)\]~ix';
		preg_match_all($pattern, $eventObj->text, $quoteMatches);
		$textArray = preg_split($pattern, $eventObj->text);
		$text = $textArray[0];
		
		$openQuotes = 0;
		$quote = '';
		foreach ($quoteMatches[0] as $i => $quoteTag) {
			if (mb_substr($quoteTag, 1, 1) == '/') {
				$openQuotes--;
				
				$quote .= $quoteTag;
				if ($openQuotes) {
					$quote .= $textArray[$i + 1];
				}
				else {
					$text .= StringStack::pushToStringStack($quote, 'preParserUserMentions', '@@@').$textArray[$i + 1];
					$quote = '';
				}
			}
			else {
				$openQuotes++;
				$quote .= $quoteTag.$textArray[$i + 1];
			}
		}
		
		$userRegex->match($text, true);
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
					$users[mb_strtolower($user->username)] = $user; 
				}
				
				$text = $userRegex->replace($text, new Callback(function ($matches) use ($users) {
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
		
		// reinsert cached quotes
		$eventObj->text = StringStack::reinsertStrings($text, 'preParserUserMentions');
	}
	
	/**
	 * Returns the username for the given regular expression match.
	 * 
	 * @param	string		$match
	 * @return	string
	 */
	public static function getUsername($match) {
		// remove escaped single quotation mark
		$match = str_replace("''", "'", $match);
		
		// remove single quotation marks
		if ($match{0} == "'") {
			$match = mb_substr($match, 1, -1);
		}
		
		return mb_strtolower($match);
	}
}
