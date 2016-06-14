<?php
namespace wcf\system\bbcode\highlighter;
use wcf\system\Callback;
use wcf\system\Regex;
use wcf\util\StringStack;
use wcf\util\StringUtil;

/**
 * Highlights syntax of (x)html documents including style and script blocks.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode\Highlighter
 */
class HtmlHighlighter extends XmlHighlighter {
	/**
	 * @inheritDoc
	 */
	protected function cacheComments($string) {
		// cache inline scripts and inline css
		$string = $this->cacheScriptsAndStyles($string);
		
		return parent::cacheComments($string);
	}
	
	/**
	 * Caches scripts and styles in the given string.
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	protected function cacheScriptsAndStyles($string) {
		$regex = new Regex('(<(style|script)[^>]*>)(.*?)(</\\2>)', Regex::CASE_INSENSITIVE | Regex::DOT_ALL);
		
		return $regex->replace($string, new Callback(function ($matches) {
			$type = ($matches[2] === 'script') ? 'js' : 'css';
			
			// strip slashes
			$content = str_replace('\\"', '"', $matches[3]);
			$openingTag = str_replace('\\"', '"', $matches[1]);
			$closingTag = str_replace('\\"', '"', $matches[4]);
			
			if (StringUtil::trim($content) == '') return $matches[0];
			
			$class = '\wcf\system\bbcode\highlighter\\'.ucfirst($type).'Highlighter';
			
			/** @noinspection PhpUndefinedMethodInspection */
			return $openingTag.StringStack::pushToStringStack('<span class="'.$type.'Highlighter">'.$class::getInstance()->highlight($content).'</span>', 'htmlHighlighter'.ucfirst($type)).$closingTag;
		}));
	}
	
	/**
	 * @inheritDoc
	 */
	protected function highlightComments($string) {
		$string = parent::highlightComments($string);
		
		// highlight script and style blocks
		$string = StringStack::reinsertStrings($string, 'htmlHighlighterJs');
		$string = StringStack::reinsertStrings($string, 'htmlHighlighterCss');
		
		return $string;
	}
}
