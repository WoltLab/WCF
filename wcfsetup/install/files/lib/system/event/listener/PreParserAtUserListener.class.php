<?php
namespace wcf\system\event\listener;
use wcf\data\bbcode\BBCode;
use wcf\data\user\UserList;
use wcf\system\request\LinkHandler;
use wcf\system\Callback;
use wcf\system\Regex;
use wcf\util\StringStack;

/**
 * Parses @user mentions.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.event.listener
 * @category	Community Framework
 */
class PreParserAtUserListener implements IParameterizedEventListener {
	/**
	 * @see	\wcf\system\event\listener\IParameterizedEventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		if (!$eventObj->text) return;
		
		// check if needed url BBCode is allowed
		if ($eventObj->allowedBBCodes !== null && !BBCode::isAllowedBBCode('url', $eventObj->allowedBBCodes)) {
			return;
		}
		
		static $userRegex = null;
		if ($userRegex === null) {
			$userRegex = new Regex("
				(?:^|(?<=\s|\]))					# either at start of string, or after whitespace
				@
				(
					([^',\s][^,\s]{2,})(?:\s[^,\s]+)?	# either at most two strings, not containing
										# whitespace or the comma, not starting with a single quote
										# separated by a single whitespace character
				|
					'(?:''|[^']){3,}'			# or a string delimited by single quotes
				)
			", Regex::IGNORE_WHITESPACE);
		}
		
		// cache quotes
		// @see	\wcf\system\bbcode\BBCodeParser::buildTagArray()
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
		
		if ($quote) {
			$text .= $quote;
		}
		
		$userRegex->match($text, true, Regex::ORDER_MATCH_BY_SET);
		$matches = $userRegex->getMatches();
		
		if (!empty($matches)) {
			$usernames = array();
			foreach ($matches as $match) {
				// we don't care about the full match
				array_shift($match);
				
				foreach ($match as $username) {
					$username = self::getUsername($username);
					if (!in_array($username, $usernames)) $usernames[] = $username;
				}
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
					// containing the full match
					$usernames = array($matches[1]);
					
					// containing only the part before the first space
					if (isset($matches[2])) $usernames[] = $matches[2];
					
					$usernames = array_map(array('\wcf\system\event\listener\PreParserAtUserListener', 'getUsername'), $usernames);
					
					foreach ($usernames as $username) {
						if (!isset($users[$username])) continue;
						$link = LinkHandler::getInstance()->getLink('User', array(
							'appendSession' => false,
							'object' => $users[$username]
						));
						
						$mention = "[url='".$link."']@".$users[$username]->username.'[/url]';
						
						// check if only the part before the first space matched, in that case append the second word
						if (isset($matches[2]) && strcasecmp($matches[2], $username) === 0) {
							$mention .= mb_substr($matches[1], strlen($matches[2]));
						}
						
						return $mention;
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
