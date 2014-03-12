<?php
namespace wcf\system\bbcode;
use wcf\data\smiley\SmileyCache;
use wcf\system\event\EventHandler;
use wcf\system\SingletonFactory;
use wcf\util\StringUtil;

/**
 * Parses urls and smilies in simple messages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bbcode
 * @category	Community Framework
 */
class SimpleMessageParser extends SingletonFactory {
	/**
	 * forbidden characters
	 * @var	string
	 */
	protected static $illegalChars = '[^\x0-\x2C\x2E\x2F\x3A-\x40\x5B-\x60\x7B-\x7F]+';
	
	/**
	 * list of smilies
	 * @var	array<\wcf\data\smiley\Smiley>
	 */
	protected $smilies = array();
	
	/**
	 * currently parsed message
	 * @var	string
	 */
	public $message = '';
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		parent::init();
		
		if (MODULE_SMILEY == 1) {
			// get smilies
			$smilies = SmileyCache::getInstance()->getSmilies();
			$categories = SmileyCache::getInstance()->getCategories();
			foreach ($smilies as $categoryID => $categorySmilies) {
				if ($categories[$categoryID ?: null]->isDisabled) continue;
				
				foreach ($categorySmilies as $smiley) {
					foreach ($smiley->smileyCodes as $smileyCode) {
						$this->smilies[$smileyCode] = '<img src="'.$smiley->getURL().'" alt="'.StringUtil::encodeHTML($smiley->smileyCode).'" />';
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
		
		// call event
		EventHandler::getInstance()->fireAction($this, 'beforeParsing');
		
		// encode html
		$this->message = StringUtil::encodeHTML($this->message);
		
		// converts newlines to <br />'s
		$this->message = nl2br($this->message);
		
		// parse urls
		if ($parseURLs) {
			$this->message = $this->parseURLs($this->message);
		}
		
		// parse smilies
		if ($parseSmilies) {
			$this->message = $this->parseSmilies($this->message);
		}
		
		// replace bad html tags (script etc.)
		$badSearch = array('/(javascript):/i', '/(about):/i', '/(vbscript):/i');
		$badReplace = array('$1<b></b>:', '$1<b></b>:', '$1<b></b>:');
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
				(?:[a-z]{2,4}(?=\b))
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
		$text = preg_replace_callback($urlPattern, array($this, 'parseURLsCallback'), $text);
		
		// parse emails
		if (mb_strpos($text, '@') !== false) {
			$text = preg_replace($emailPattern, '<a href="mailto:\\0">\\0</a>', $text);
		}
		
		return $text;
	}
	
	/**
	 * Callback for preg_replace.
	 * 
	 * @see	\wcf\system\bbcode\SimpleMessageParser::parseURLs()
	 */
	protected function parseURLsCallback($matches) {
		$url = StringUtil::decodeHTML($matches[0]);
		
		// add protocol if necessary
		if (!preg_match("/[a-z]:\/\//si", $url)) {
			$url = 'http://'.$url;
		}
		
		return StringUtil::getAnchorTag($url);
	}
	
	/**
	 * Parses smiley codes.
	 * 
	 * @param	string		$text
	 * @return	string		text
	 */
	public function parseSmilies($text) {
		foreach ($this->smilies as $code => $html) {
			//$text = preg_replace('~(?<!&\w{2}|&\w{3}|&\w{4}|&\w{5}|&\w{6}|&#\d{2}|&#\d{3}|&#\d{4}|&#\d{5})'.preg_quote(StringUtil::encodeHTML($code), '~').'(?![^<]*>)~', $html, $text);
			$text = preg_replace('~(?<=^|\s)'.preg_quote(StringUtil::encodeHTML($code), '~').'(?=$|\s|<br />)~', $html, $text);
		}
		
		return $text;
	}
}
