<?php
namespace wcf\system\bbcode;
use wcf\data\bbcode\media\provider\BBCodeMediaProvider;
use wcf\data\bbcode\BBCode;
use wcf\data\bbcode\BBCodeCache;
use wcf\system\event\EventHandler;
use wcf\system\Callback;
use wcf\system\Regex;
use wcf\system\SingletonFactory;
use wcf\util\StringStack;

/**
 * Parses message before inserting them into the database.
 * 
 * @author	Tim Duesterhus, Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bbcode
 * @category	Community Framework
 */
class PreParser extends SingletonFactory {
	/**
	 * forbidden characters
	 * @var	string
	 */
	protected static $illegalChars = '[^\x0-\x2C\x2E\x2F\x3A-\x40\x5B-\x60\x7B-\x7F]+';
	
	/**
	 * list of allowed bbcode tags
	 * @var	array<string>
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
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$sourceCodeTags = array();
		foreach (BBCodeCache::getInstance()->getBBCodes() as $bbcode) {
			if ($bbcode->isSourceCode) $sourceCodeTags[] = $bbcode->bbcodeTag;
		}
		if (!empty($sourceCodeTags)) $this->sourceCodeRegEx = implode('|', $sourceCodeTags);
	}
	
	/**
	 * Preparses the given text.
	 * 
	 * @param	string			$text
	 * @param	array<string>		$allowedBBCodes
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
				return '['.StringStack::pushToStringStack(mb_substr($matches[0], 1, -1), 'preParserCode', "\0\0\0").']';
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
}
