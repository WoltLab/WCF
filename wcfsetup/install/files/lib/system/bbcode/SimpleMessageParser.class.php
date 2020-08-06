<?php
namespace wcf\system\bbcode;
use wcf\data\smiley\Smiley;
use wcf\data\smiley\SmileyCache;
use wcf\system\event\EventHandler;
use wcf\system\SingletonFactory;
use wcf\util\StringUtil;

/**
 * Parses urls and smilies in simple messages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode
 */
class SimpleMessageParser extends SingletonFactory {
	/**
	 * forbidden characters
	 * @var	string
	 */
	protected static $illegalChars = '[^\x0-\x2C\x2E\x2F\x3A-\x40\x5B-\x60\x7B-\x7F]+';
	
	/**
	 * list of smilies
	 * @var	Smiley[]
	 */
	protected $smilies = [];
	
	/**
	 * cached URLs
	 * @var	string[]
	 */
	protected $cachedURLs = [];
	
	/**
	 * cached e-mails
	 * @var	string[]
	 */
	protected $cachedEmails = [];
	
	/**
	 * currently parsed message
	 * @var	string
	 */
	public $message = '';
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		parent::init();
		
		if (MODULE_SMILEY == 1) {
			// get smilies
			$smilies = SmileyCache::getInstance()->getSmilies();
			$categories = SmileyCache::getInstance()->getCategories();
			foreach ($smilies as $categoryID => $categorySmilies) {
				if ($categories[$categoryID ?: null]->isDisabled) continue;
				
				/** @var Smiley $smiley */
				foreach ($categorySmilies as $smiley) {
					foreach ($smiley->smileyCodes as $smileyCode) {
						$this->smilies[$smileyCode] = $smiley->getHtml();
					}
				}
			}
			krsort($this->smilies);
		}
	}
	
	/**
	 * Parses the given message and returns the parsed message.
	 * 
	 * @param	string		$message
	 * @param	boolean		$parseURLs
	 * @param	boolean		$parseSmilies
	 * @return	string
	 */
	public function parse($message, $parseURLs = true, $parseSmilies = true) {
		$this->message = $message;
		$this->cachedURLs = $this->cachedEmails = [];
		
		// call event
		EventHandler::getInstance()->fireAction($this, 'beforeParsing');
		
		// parse urls
		if ($parseURLs) {
			$this->message = $this->parseURLs($this->message);
		}
		
		// encode html
		$this->message = StringUtil::encodeHTML($this->message);
		
		// converts newlines to <br>'s
		$this->message = nl2br($this->message, false);
		
		// parse urls
		if ($parseURLs) {
			$this->message = $this->insertCachedURLs($this->message);
		}
		
		// parse smilies
		if ($parseSmilies) {
			$this->message = $this->parseSmilies($this->message);
		}
		
		// replace bad html tags (script etc.)
		$badSearch = ['/(javascript):/i', '/(about):/i', '/(vbscript):/i'];
		$badReplace = ['$1<b></b>:', '$1<b></b>:', '$1<b></b>:'];
		$this->message = preg_replace($badSearch, $badReplace, $this->message);
		
		// call event
		EventHandler::getInstance()->fireAction($this, 'afterParsing');
		
		return $this->message;
	}
	
	/**
	 * Parses urls.
	 * 
	 * @param	string		$text
	 * @return	string		text
	 */
	public function parseURLs($text) {
		// define pattern
		$urlPattern = '~(?<!\B|"|\'|=|/|\]|,|\?)
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
			)?
			~ix';
		$emailPattern = '~(?<!\B|"|\'|=|/|\]|,|:)
			(?:)
			\w+(?:[\.\-]\w+)*
			@
			(?:'.static::$illegalChars.'\.)+		# hostname
			(?:[a-z]{2,4}(?=\b))
			(?!"|\'|\[|\-)
			~ix';
		
		// parse urls
		$text = preg_replace_callback($urlPattern, [$this, 'cacheURLsCallback'], $text);
		
		// parse emails
		if (mb_strpos($text, '@') !== false) {
			$text = preg_replace_callback($emailPattern, [$this, 'cacheEmailsCallback'], $text);
		}
		
		return $text;
	}
	
	/**
	 * Returns the hash for an matched URL in the message.
	 * 
	 * @param	array		$matches
	 * @return	string
	 */
	protected function cacheURLsCallback($matches) {
		$hash = '@@'.StringUtil::getRandomID().'@@';
		$this->cachedURLs[$hash] = $matches[0];
		
		return $hash;
	}
	
	/**
	 * Returns the hash for an matched e-mail in the message.
	 * 
	 * @param	array		$matches
	 * @return	string
	 */
	protected function cacheEmailsCallback($matches) {
		$hash = '@@'.StringUtil::getRandomID().'@@';
		$this->cachedEmails[$hash] = $matches[0];
		
		return $hash;
	}
	
	/**
	 * Reinserts cached URLs and e-mails.
	 * 
	 * @param	string		$text
	 * @return	string
	 */
	protected function insertCachedURLs($text) {
		foreach ($this->cachedURLs as $hash => $url) {
			// add protocol if necessary
			if (!preg_match("/[a-z]:\/\//si", $url)) {
				$url = 'http://'.$url;
			}
			
			$text = str_replace($hash, StringUtil::getAnchorTag($url, '', true, true), $text);
		}
		
		foreach ($this->cachedEmails as $hash => $email) {
			$email = StringUtil::encodeHTML($email);
				
			$text = str_replace($hash, '<a href="mailto:'.$email.'">'.$email.'</a>', $text);
		}
		
		return $text;
	}
	
	/**
	 * Parses smiley codes.
	 * 
	 * @param	string		$text
	 * @return	string		text
	 */
	public function parseSmilies($text) {
		$smileyCount = 0;
		foreach ($this->smilies as $code => $html) {
			$text = preg_replace_callback('~(?<=^|\s)'.preg_quote(StringUtil::encodeHTML($code), '~').'(?=$|\s|<br />|<br>)~', function() use ($code, $html, &$smileyCount) {
				if ($smileyCount === 50) {
					return $code;
				}
				
				$smileyCount++;
				return $html;
			}, $text);
		}
		
		return $text;
	}
}
