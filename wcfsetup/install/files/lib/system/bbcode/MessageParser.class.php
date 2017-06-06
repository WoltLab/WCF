<?php
namespace wcf\system\bbcode;
use wcf\data\bbcode\attribute\BBCodeAttribute;
use wcf\data\smiley\SmileyCache;
use wcf\system\event\EventHandler;
use wcf\util\StringUtil;

/**
 * Parses bbcode tags, smilies etc. in messages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bbcode
 * @category	Community Framework
 */
class MessageParser extends BBCodeParser {
	/**
	 * list of smilies
	 * @var	array<\wcf\data\smiley\Smiley>
	 */
	protected $smilies = array();
	
	/**
	 * cached bbcodes
	 * @var	array
	 */
	protected $cachedCodes = array();
	
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
	 * Parses a message.
	 * 
	 * @param	string		$message
	 * @param	boolean		$enableSmilies
	 * @param	boolean		$enableHtml
	 * @param	boolean		$enableBBCodes
	 * @param	boolean		$doKeywordHighlighting
	 * @return	string		parsed message
	 */
	public function parse($message, $enableSmilies = true, $enableHtml = false, $enableBBCodes = true, $doKeywordHighlighting = true) {
		$this->cachedCodes = array();
		$this->message = $message;
		
		// call event
		EventHandler::getInstance()->fireAction($this, 'beforeParsing');
		
		if ($enableBBCodes) {
			// cache codes
			$this->message = $this->cacheCodes($this->message);
		}
		
		if (!$enableHtml) {
			// encode html
			$this->message = StringUtil::encodeHTML($this->message);
			
			// converts newlines to <br />'s
			if ($this->getOutputType() == 'text/html') {
				$this->message = nl2br($this->message);
			}
		}
		else {
			if ($this->getOutputType() == 'text/simplified-html') {
				$this->message = StringUtil::stripHTML($this->message);
			}
		}
		
		// parse bbcodes
		if ($enableBBCodes) {
			$this->message = parent::parse($this->message);
		}
		
		// parse smilies
		if ($enableSmilies) {
			$this->message = $this->parseSmilies($this->message, $enableHtml);
		}
		
		if ($enableBBCodes && !empty($this->cachedCodes)) {
			// insert cached codes
			$this->message = $this->insertCachedCodes($this->message);
		}
		
		// highlight search query
		if ($doKeywordHighlighting) {
			$this->message = KeywordHighlighter::getInstance()->doHighlight($this->message);
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
	 * Parses smiley codes.
	 * 
	 * @param	string		$text
	 * @return	string		text
	 */
	protected function parseSmilies($text, $enableHtml = false) {
		$codes = array_keys($this->smilies);
		$pattern = '~(?<=^|\s|<li>)'.str_replace('@', '|', preg_quote((!$enableHtml ? StringUtil::encodeHTML(implode('@', $codes)) : implode('|', $codes)), '~')).'(?=$|\s|</li>'.(!$enableHtml ? '|<br />' : '').')~';
		$matches = array();
		preg_match_all($pattern, $text, $matches);
		if (isset($matches[0])) {
			foreach ($matches[0] as $key => $value) {
				$value = StringUtil::decodeHTML($value);
				$text = preg_replace('~(?<=^|\s|<li>)'.preg_quote((!$enableHtml ? StringUtil::encodeHTML($value) : $value), '~').'(?=$|\s|</li>'.(!$enableHtml ? '|<br />' : '').')~', $this->smilies[$value], $text);
			}
		}
		
		return $text;
	}
	
	/**
	 * Caches code bbcodes to avoid parsing of smileys and other bbcodes inside them.
	 * 
	 * @param	string		$text
	 * @return	string
	 */
	protected function cacheCodes($text) {
		if (!empty($this->sourceCodeRegEx)) {
			$text = preg_replace_callback("~(\[(".$this->sourceCodeRegEx.")
				(?:=
					(?:\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|[^,\]]*)
					(?:,(?:\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|[^,\]]*))*
				)?\])
				(.*?)
				(?:\[/\\2\])~six", array($this, 'cacheCodesCallback'), $text);
		}
		return $text;
	}
	
	/**
	 * Returns the hash for an matched code bbcode in the message.
	 * 
	 * @param	array		$matches
	 * @return	string
	 */
	protected function cacheCodesCallback($matches) {
		// create hash
		$hash = '@@'.StringUtil::getHash(uniqid(microtime()).$matches[3]).'@@';
		
		// build tag
		$tag = $this->buildTag($matches[1]);
		$tag['content'] = $matches[3];
		
		// save tag
		$this->cachedCodes[$hash] = $tag;
		
		return $hash;
	}
	
	/**
	 * Reinserts cached code bbcodes.
	 * 
	 * @param	string		$text
	 * @return	string
	 */
	protected function insertCachedCodes($text) {
		foreach ($this->cachedCodes as $hash => $tag) {
			// build code and insert
			if ($this->bbcodes[$tag['name']]->className) {
				$replacement = $this->bbcodes[$tag['name']]->getProcessor()->getParsedTag($tag, $tag['content'], $tag, $this);
			}
			else {
				$replacement = $this->buildOpeningTag($tag) . StringUtil::encodeHTML($tag['content']) . $this->buildClosingTag($tag);
			}
			
			$text = str_replace($hash, $replacement, $text);
		}
		
		return $text;
	}
	
	/**
	 * @see	\wcf\system\bbcode\BBCodeParser::isValidTagAttribute()
	 */
	protected function isValidTagAttribute(array $tagAttributes, BBCodeAttribute $definedTagAttribute) {
		if (!parent::isValidTagAttribute($tagAttributes, $definedTagAttribute)) {
			return false;
		}
		
		// check for cached codes
		if (isset($tagAttributes[$definedTagAttribute->attributeNo]) && preg_match('/@@[a-f0-9]{40}@@/', $tagAttributes[$definedTagAttribute->attributeNo])) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Returns a text-only version of given message.
	 * 
	 * @param	string		$message
	 * @return	string
	 */
	public function stripHTML($message) {
		// remove img tags (smilies)
		$message = preg_replace('~<img src="[^"]+" alt="([^"]+)" />~', '\\1', $message);
		
		// strip other HTML tags
		$message = StringUtil::stripHTML($message);
		
		// decode HTML entities
		$message = StringUtil::decodeHTML($message);
		
		return $message;
	}
}
