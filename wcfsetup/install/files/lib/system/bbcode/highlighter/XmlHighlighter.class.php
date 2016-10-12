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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode\Highlighter
 */
class XmlHighlighter extends Highlighter {
	/**
	 * @inheritDoc
	 */
	protected $allowsNewslinesInQuotes = true;
	
	/**
	 * @inheritDoc
	 */
	protected $quotes = ['"'];
	
	/**
	 * @inheritDoc
	 */
	protected $singleLineComment = [];
	
	/**
	 * @inheritDoc
	 */
	protected $commentStart = ["<!--"];
	
	/**
	 * @inheritDoc
	 */
	protected $commentEnd = ["-->"];
	
	/**
	 * @inheritDoc
	 */
	protected $separators = ["<", ">"];
	
	/**
	 * @inheritDoc
	 */
	protected $operators = [];
	
	const XML_ATTRIBUTE_NAME = '[a-z0-9](?:(?:(?<!-)-)?[a-z0-9])*';
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	protected function highlightQuotes($string) {
		return StringStack::reinsertStrings(parent::highlightQuotes($string), 'highlighterQuotes');
	}
	
	/**
	 * @inheritDoc
	 */
	protected function highlightNumbers($string) {
		// do not highlight numbers
		return $string;
	}
}
