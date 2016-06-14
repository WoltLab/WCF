<?php
namespace wcf\system\bbcode;
use wcf\data\bbcode\media\provider\BBCodeMediaProvider;
use wcf\data\bbcode\BBCode;
use wcf\data\bbcode\BBCodeCache;
use wcf\data\user\UserList;
use wcf\system\event\EventHandler;
use wcf\system\request\LinkHandler;
use wcf\system\Callback;
use wcf\system\Regex;
use wcf\system\SingletonFactory;
use wcf\util\StringStack;

/**
 * Parses message before inserting them into the database.
 * 
 * @author	Tim Duesterhus, Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode
 */
class PreParser extends SingletonFactory {
	/**
	 * forbidden characters
	 * @var	string
	 */
	protected static $illegalChars = '[^\x0-\x2C\x2E\x2F\x3A-\x40\x5B-\x60\x7B-\x7F]+';
	
	/**
	 * list of allowed bbcode tags
	 * @var	string[]
	 */
	public $allowedBBCodes = null;
	
	/**
	 * regular expression for source codes
	 * @var	string
	 */
	protected $sourceCodeRegEx = '';
	
	/**
	 * text
	 * @var	string
	 */
	public $text = '';
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		$sourceCodeTags = [];
		foreach (BBCodeCache::getInstance()->getBBCodes() as $bbcode) {
			if ($bbcode->isSourceCode) $sourceCodeTags[] = $bbcode->bbcodeTag;
		}
		if (!empty($sourceCodeTags)) $this->sourceCodeRegEx = implode('|', $sourceCodeTags);
	}
	
	/**
	 * Preparses the given text.
	 * 
	 * @param	string			$text
	 * @param	string[]		$allowedBBCodes
	 * @return	string
	 */
	public function parse($text, array $allowedBBCodes = null) {
		$this->text = $text;
		$this->allowedBBCodes = $allowedBBCodes;
		
		// cache codes
		$this->cacheCodes();
		
		// cache url bbcodes
		$this->cacheURLBBCodes();
		
		// call event
		EventHandler::getInstance()->fireAction($this, 'beforeParsing');
		
		// parse user mentions
		if ($this->allowedBBCodes === null || BBCode::isAllowedBBCode('url', $this->allowedBBCodes)) {
			$this->parseUserMentions();
		}
		
		// parse urls
		if ($this->allowedBBCodes === null || BBCode::isAllowedBBCode('media', $this->allowedBBCodes) || BBCode::isAllowedBBCode('url', $this->allowedBBCodes)) {
			$this->parseURLs();
		}
		
		// parse email addresses
		if ($this->allowedBBCodes === null || BBCode::isAllowedBBCode('email', $this->allowedBBCodes)) {
			$this->parseEmails();
		}
		
		// call event
		EventHandler::getInstance()->fireAction($this, 'afterParsing');
		
		// insert cached url bbcodes
		$this->insertCachedURLBBCodes();
		
		// insert cached codes
		$this->insertCachedCodes();
		
		return $this->text;
	}
	
	/**
	 * Handles pre-parsing of email addresses.
	 */
	protected function parseEmails() {
		if (mb_strpos($this->text, '@') === false) return;
		
		static $emailPattern = null;
		if ($emailPattern === null) {
			$emailPattern = new Regex('
			(?<!\B|"|\'|=|/|,|:)
			(?:)
			\w+(?:[\.\-]\w+)*
			@
			(?:'.self::$illegalChars.'\.)+		# hostname
			(?:[a-z]{2,4}(?=\b))
			(?!"|\'|\-|\]|\.[a-z])', Regex::IGNORE_WHITESPACE | Regex::CASE_INSENSITIVE);
		}
		
		$this->text = $emailPattern->replace($this->text, '[email]\\0[/email]');
	}
	
	/**
	 * Handles pre-parsing of URLs.
	 */
	protected function parseURLs() {
		static $urlPattern = null;
		static $callback = null;
		if ($urlPattern === null) {
			$urlPattern = new Regex('
			(?<!\B|"|\'|=|/|,|\?|\.)
			(?:						# hostname
				(?:ftp|https?)://'.static::$illegalChars.'(?:\.'.static::$illegalChars.')*
				|
				www\.(?:'.static::$illegalChars.'\.)+
				(?:[a-z]{2,63}(?=\b))			# tld
			)
			
			(?::\d+)?					# port
			
			(?:
				/
				[^!.,?;"\'<>()\[\]{}\s]*
				(?:
					[!.,?;(){}]+ [^!.,?;"\'<>()\[\]{}\s]+
				)*
			)?', Regex::IGNORE_WHITESPACE | Regex::CASE_INSENSITIVE);
		}
		if ($callback === null) {
			$callback = new Callback(function ($matches) {
				if ((PreParser::getInstance()->allowedBBCodes === null || BBCode::isAllowedBBCode('media', PreParser::getInstance()->allowedBBCodes)) && BBCodeMediaProvider::isMediaURL($matches[0])) {
					return '[media]'.$matches[0].'[/media]';
				}
				
				if (PreParser::getInstance()->allowedBBCodes === null || BBCode::isAllowedBBCode('url', PreParser::getInstance()->allowedBBCodes)) {
					return '[url]'.$matches[0].'[/url]';
				}
				
				return $matches[0];
			});
		}
		
		$this->text = $urlPattern->replace($this->text, $callback);
	}
	
	/**
	 * Parses user mentions.
	 * 
	 * @since	3.0
	 */
	protected function parseUserMentions() {
		static $userRegex = null;
		if ($userRegex === null) {
			$userRegex = new Regex("
				(?:^|(?<=\s|\]))				# either at start of string, or after whitespace
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
		preg_match_all($pattern, $this->text, $quoteMatches);
		$textArray = preg_split($pattern, $this->text);
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
			$usernames = [];
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
				$userList->getConditionBuilder()->add('user_table.username IN (?)', [$usernames]);
				$userList->readObjects();
				$users = [];
				foreach ($userList as $user) {
					$users[mb_strtolower($user->username)] = $user;
				}
				
				$text = $userRegex->replace($text, new Callback(function ($matches) use ($users) {
					// containing the full match
					$usernames = [$matches[1]];
					
					// containing only the part before the first space
					if (isset($matches[2])) $usernames[] = $matches[2];
					
					$usernames = array_map([PreParser::class, 'getUsername'], $usernames);
					
					foreach ($usernames as $username) {
						if (!isset($users[$username])) continue;
						$link = LinkHandler::getInstance()->getLink('User', [
							'appendSession' => false,
							'object' => $users[$username]
						]);
						
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
		$this->text = StringStack::reinsertStrings($text, 'preParserUserMentions');
	}
	
	/**
	 * Caches code bbcodes to avoid parsing inside them.
	 */
	protected function cacheCodes() {
		if (!empty($this->sourceCodeRegEx)) {
			static $bbcodeRegex = null;
			static $callback = null;
			
			if ($bbcodeRegex === null) {
				$bbcodeRegex = new Regex("
				(\[(".$this->sourceCodeRegEx.")
				(?:=
					(?:\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|[^,\]]*)
					(?:,(?:\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|[^,\]]*))*
				)?\])
				(.*?)
				(?:\[/\\2\])", Regex::DOT_ALL | Regex::IGNORE_WHITESPACE | Regex::CASE_INSENSITIVE);
				
				$callback = new Callback(function ($matches) {
					return '['.StringStack::pushToStringStack(mb_substr($matches[0], 1, -1), 'preParserCode', "\0\0\0").']';
				});
			}
			
			$this->text = $bbcodeRegex->replace($this->text, $callback);
		}
	}
	
	/**
	 * Reinserts cached code bbcodes.
	 */
	protected function insertCachedCodes() {
		$this->text = StringStack::reinsertStrings($this->text, 'preParserCode');
	}
	
	/**
	 * Caches all bbcodes that contain URLs.
	 */
	protected function cacheURLBBCodes() {
		static $bbcodeRegex = null;
		static $callback = null;
		
		if ($bbcodeRegex === null) {
			$bbcodeRegex = new Regex("
				(?:\[quote
				(?:=
					(?:\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|[^,\]]*)
					(?:,(?:\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|[^,\]]*))*
				)?\])
				|
				(?:\[(url|media|email|img)
				(?:=
					(?:\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|[^,\]]*)
					(?:,(?:\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|[^,\]]*))*
				)?\])
				(.*?)
				(?:\[/\\1\])", Regex::DOT_ALL | Regex::IGNORE_WHITESPACE | Regex::CASE_INSENSITIVE);
			
			$callback = new Callback(function ($matches) {
				return '['.StringStack::pushToStringStack(mb_substr($matches[0], 1, -1), 'urlBBCodes', "\0\0\0").']';
			});
		}
		
		$this->text = $bbcodeRegex->replace($this->text, $callback);
	}
	
	/**
	 * Reinserts cached url bbcodes.
	 */
	protected function insertCachedURLBBCodes() {
		$this->text = StringStack::reinsertStrings($this->text, 'urlBBCodes');
	}
	
	/**
	 * Returns the username for the given regular expression match.
	 * 
	 * @param	string		$match
	 * @return	string
	 * @since	3.0
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
