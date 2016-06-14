<?php
namespace wcf\system\bbcode\highlighter;
use wcf\system\Callback;
use wcf\system\Regex;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\StringStack;
use wcf\util\StringUtil;

/**
 * Highlights syntax of source code.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode\Highlighter
 */
abstract class Highlighter extends SingletonFactory {
	/**
	 * allow multiline quotes
	 * @var	boolean
	 */
	protected $allowsNewslinesInQuotes = false;
	
	/**
	 * comment end delimiter
	 * @var	string[]
	 */
	protected $commentEnd = ["*/"];
	
	/**
	 * comment start delimiter
	 * @var	string[]
	 */
	protected $commentStart = ["/*"];
	
	/**
	 * escape sequence
	 * @var	string[]
	 */
	protected $escapeSequence = ["\\"];
	
	/**
	 * categorized keywords
	 * @var	string[]
	 */
	protected $keywords1 = [];
	
	/**
	 * categorized keywords
	 * @var	string[]
	 */
	protected $keywords2 = [];
	
	/**
	 * categorized keywords
	 * @var	string[]
	 */
	protected $keywords3 = [];
	
	/**
	 * categorized keywords
	 * @var	string[]
	 */
	protected $keywords4 = [];
	
	/**
	 * categorized keywords
	 * @var	string[]
	 */
	protected $keywords5 = [];
	
	/**
	 * list of arithmetic operators
	 * @var	string[]
	 */
	protected $operators = [];
	
	/**
	 * list of quote marks
	 * @var	string[]
	 */
	protected $quotes = ["'", '"'];
	
	/**
	 * list of separator sequences
	 * @var	string[]
	 */
	protected $separators = [];
	
	/**
	 * inline comment sequence
	 * @var	string[]
	 */
	protected $singleLineComment = ["//"];
	
	/**
	 * regular expression to extract comments
	 * @var	\wcf\system\Regex
	 */
	public $cacheCommentsRegEx = null;
	
	/**
	 * regular expression to find quote marks
	 * @var	\wcf\system\Regex
	 */
	public $quotesRegEx = null;
	
	/**
	 * regular expression to find string separators
	 * @var	string
	 */
	public $separatorsRegEx = '';
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		$this->buildRegularExpressions();
	}
	
	/**
	 * Returns the title of this highlighter.
	 * 
	 * @return	string
	 */
	public function getTitle() {
		// regex to extract the Highlighter out of the namespaced classname
		$reType = new Regex('\\\\?wcf\\\\system\\\\bbcode\\\\highlighter\\\\(.*)Highlighter', Regex::CASE_INSENSITIVE);
		
		return WCF::getLanguage()->get('wcf.bbcode.code.'.$reType->replace(strtolower(get_class($this)), '\1').'.title');
	}
	
	/**
	 * Highlights syntax of source code.
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	public function highlight($string) {
		// cache comments
		$string = $this->cacheComments($string);
		
		// cache quotes
		$string = $this->cacheQuotes($string);
		
		// encode html
		$string = StringUtil::encodeHTML($string);
		
		// do highlight
		$string = $this->highlightOperators($string);
		$string = $this->highlightKeywords($string);
		$string = $this->highlightNumbers($string);
		
		// insert and highlight quotes
		$string = $this->highlightQuotes($string);
		
		// insert and highlight comments
		$string = $this->highlightComments($string);
		
		return $string;
	}
	
	/**
	 * Builds regular expressions.
	 */
	protected function buildRegularExpressions() {
		// quotes regex
		$quotedEscapeSequence = preg_quote(implode('', $this->escapeSequence));
		$quotesRegEx = '';
		foreach ($this->quotes as $quote) {
			if ($quotesRegEx !== '') $quotesRegEx .= '|';
			if (!is_array($quote)) $quote = [$quote, $quote];
			list($opening, $closing) = $quote;
			
			$opening = preg_quote($opening);
			$closing = preg_quote($closing);
			
			if ($quotedEscapeSequence) {
				$quotesRegEx .= $opening.'(?>[^'.$closing.$quotedEscapeSequence.']|'.$quotedEscapeSequence.'.)*'.$closing;
			}
			else {
				$quotesRegEx .= $opening.'(?>[^'.$closing.$quotedEscapeSequence.'])*'.$closing;
			}
		}
		
		if ($quotesRegEx !== '') {
			$quotesRegEx = '(?:'.$quotesRegEx.')';
			$this->quotesRegEx = new Regex($quotesRegEx, ($this->allowsNewslinesInQuotes) ? Regex::DOT_ALL : Regex::MODIFIER_NONE);
		}
		
		// cache comment regex
		if (!empty($this->singleLineComment) || !empty($this->commentStart)) {
			$cacheCommentsRegEx = '';
			
			if ($quotesRegEx !== '') {
				$cacheCommentsRegEx .= "(".$quotesRegEx.")|";
			}
			else {
				$cacheCommentsRegEx .= "()";
			}
			
			$cacheCommentsRegEx .= "(";
			if (!empty($this->commentStart)) {
				$cacheCommentsRegEx .= '(?:'.implode('|', array_map('preg_quote', $this->commentStart)).').*?(?:'.implode('|', array_map('preg_quote', $this->commentEnd)).')';
				
				if (!empty($this->singleLineComment)) {
					$cacheCommentsRegEx .= '|';
				}
			}
			
			if (!empty($this->singleLineComment)) {
				$cacheCommentsRegEx .= "(?:".implode('|', array_map('preg_quote', $this->singleLineComment)).")[^\n]*";
			}
			
			$cacheCommentsRegEx .= ")";
			
			$this->cacheCommentsRegEx = new Regex($cacheCommentsRegEx, Regex::DOT_ALL);
		}
		
		$this->separatorsRegEx = StringUtil::encodeHTML(implode('|', array_map('preg_quote', $this->separators))).'|\s|&nbsp;|^|$|>|<';
	}
	
	/**
	 * Caches comments.
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	protected function cacheComments($string) {
		if ($this->cacheCommentsRegEx !== null) {
			$string = $this->cacheCommentsRegEx->replace($string, new Callback(function (array $matches) {
				$string = $matches[1];
				if (isset($matches[2])) $comment = $matches[2];
				else $comment = '';
				
				$hash = '';
				if (!empty($comment)) {
					// create hash
					$hash = StringStack::pushToStringStack('<span class="hlComments">'.StringUtil::encodeHTML($comment).'</span>', 'highlighterComments');
				}
				
				return $string.$hash;
			}));
		}
		
		return $string;
	}
	
	/**
	 * Caches quotes.
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	protected function cacheQuotes($string) {
		if ($this->quotesRegEx !== null) {
			$string = $this->quotesRegEx->replace($string, new Callback(function (array $matches) {
				return StringStack::pushToStringStack('<span class="hlQuotes">'.StringUtil::encodeHTML($matches[0]).'</span>', 'highlighterQuotes');
			}));
		}
		
		return $string;
	}
	
	/**
	 * Highlights operators.
	 *
	 * @param	string		$string
	 * @return	string
	 */
	protected function highlightOperators($string) {
		if (!empty($this->operators)) {
			$string = preg_replace('!('.StringUtil::encodeHTML(implode('|', array_map('preg_quote', $this->operators))).')!i', '<span class="hlOperators">\\0</span>', $string);
		}
		
		return $string;
	}
	
	/**
	 * Highlights keywords.
	 *
	 * @param	string		$string
	 * @return	string
	 */
	protected function highlightKeywords($string) {
		$_this = $this;
		$buildKeywordRegex = function (array $keywords) use ($_this) {
			return '!(?<='.$_this->separatorsRegEx.')('.StringUtil::encodeHTML(implode('|', array_map('preg_quote', $keywords))).')(?='.$_this->separatorsRegEx.')!i';
		};
		
		if (!empty($this->keywords1)) {
			$string = preg_replace($buildKeywordRegex($this->keywords1), '<span class="hlKeywords1">\\0</span>', $string);
		}
		if (!empty($this->keywords2)) {
			$string = preg_replace($buildKeywordRegex($this->keywords2), '<span class="hlKeywords2">\\0</span>', $string);
		}
		if (!empty($this->keywords3)) {
			$string = preg_replace($buildKeywordRegex($this->keywords3), '<span class="hlKeywords3">\\0</span>', $string);
		}
		if (!empty($this->keywords4)) {
			$string = preg_replace($buildKeywordRegex($this->keywords4), '<span class="hlKeywords4">\\0</span>', $string);
		}
		if (!empty($this->keywords5)) {
			$string = preg_replace($buildKeywordRegex($this->keywords5), '<span class="hlKeywords5">\\0</span>', $string);
		}
		
		return $string;
	}
	
	/**
	 * Highlights numbers.
	 *
	 * @param	string		$string
	 * @return	string
	 */
	protected function highlightNumbers($string) {
		$string = preg_replace('!(?<='.$this->separatorsRegEx.')(-?\d+)(?='.$this->separatorsRegEx.')!i', '<span class="hlNumbers">\\0</span>', $string);
		
		return $string;
	}
	
	/**
	 * Highlights quotes.
	 *
	 * @param	string		$string
	 * @return	string
	 */
	protected function highlightQuotes($string) {
		return StringStack::reinsertStrings($string, 'highlighterQuotes');
	}
	
	/**
	 * Highlights comments.
	 *
	 * @param	string		$string
	 * @return	string
	 */
	protected function highlightComments($string) {
		return StringStack::reinsertStrings($string, 'highlighterComments');
	}
}
