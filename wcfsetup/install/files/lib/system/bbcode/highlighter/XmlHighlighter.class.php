<?php
namespace wcf\system\bbcode\highlighter;
use wcf\system\Callback;
use wcf\system\Regex;
use wcf\util\StringStack;
use wcf\util\StringUtil;

/**
 * Highlights syntax of xml sourcecode.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bbcode.highlighter
 * @category	Community Framework
 */
class XmlHighlighter extends Highlighter {
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$allowsNewslinesInQuotes
	 */
	protected $allowsNewslinesInQuotes = true;
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$quotes
	 */
	protected $quotes = array('"');
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$singleLineComment
	 */
	protected $singleLineComment = array();
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$commentStart
	 */
	protected $commentStart = array("<!--");
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$commentEnd
	 */
	protected $commentEnd = array("-->");
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$separators
	 */
	protected $separators = array("<", ">");
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::$operators
	 */
	protected $operators = array();
	
	const XML_ATTRIBUTE_NAME = '[a-z0-9](?:(?:(?<!-)-)?[a-z0-9])*';
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::highlightKeywords()
	 */
	protected function highlightKeywords($string) {
		$string = parent::highlightKeywords($string);
		// find tags
		$regex = new Regex('&lt;(?:/|\!|\?)?[a-z0-9]+(?:\s+'.self::XML_ATTRIBUTE_NAME.'(?:=[^\s/\?&]+)?)*(?:\s+/|\?)?&gt;', Regex::CASE_INSENSITIVE);
		$string = $regex->replace($string, new Callback(function ($matches) {
			// highlight attributes
			$tag = Regex::compile(XmlHighlighter::XML_ATTRIBUTE_NAME.'(?:=[^\s/\?&]+)?(?=\s|&)', Regex::CASE_INSENSITIVE)->replace($matches[0], '<span class="hlKeywords2">\\0</span>');
			
			// highlight tag
			return '<span class="hlKeywords1">'.$tag.'</span>';
		}));
		
		return $string;
	}
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::cacheQuotes()
	 */
	protected function cacheQuotes($string) {
		$string = parent::cacheQuotes($string);
		
		// highlight CDATA-Tags as quotes
		$string = Regex::compile('<!\[CDATA\[.*?\]\]>', Regex::DOT_ALL)->replace($string, new Callback(function (array $matches) {
			return StringStack::pushToStringStack('<span class="hlQuotes">'.StringUtil::encodeHTML($matches[0]).'</span>', 'highlighterQuotes');
		}));
		
		return $string;
	}
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::highlightQuotes()
	 */
	protected function highlightQuotes($string) {
		return StringStack::reinsertStrings(parent::highlightQuotes($string), 'highlighterQuotes');
	}
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::highlightNumbers()
	 */
	protected function highlightNumbers($string) {
		// do not highlight numbers
		return $string;
	}
}
