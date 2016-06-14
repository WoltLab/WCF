<?php
namespace wcf\system\bbcode\highlighter;
use wcf\system\Regex;

/**
 * Highlights syntax of PHP sourcecode.
 * 
 * @author	Tim Duesterhus, Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode\Highlighter
 */
class PhpHighlighter extends Highlighter {
	public static $colorToClass = [];
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		parent::init();
		
		$types = ['default' => 'hlKeywords1', 'keyword' => 'hlKeywords2', 'comment' => 'hlComments', 'string' => 'hlQuotes'];
		
		self::$colorToClass['<span style="color: '.ini_get('highlight.html').'">'] = '<span>';
		foreach ($types as $type => $class) {
			self::$colorToClass['<span style="color: '.ini_get('highlight.'.$type).'">'] = '<span class="'.$class.'">';
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function highlight($code) {
		// add starting php tag
		$phpTagsAdded = false;
		if (mb_strpos($code, '<?') === false) {
			$phpTagsAdded = true;
			$code = '<?php '.$code.' ?>';
		}
		
		// do highlight
		$highlightedCode = highlight_string($code, true);
		
		// clear code
		$highlightedCode = str_replace('<code>', '', $highlightedCode);
		$highlightedCode = str_replace('</code>', '', $highlightedCode);
		
		// remove added php tags
		if ($phpTagsAdded) {
			// the opening and closing PHP tags were added previously, hence we actually do
			// know that the first (last for the closing tag) occurence is the one inserted
			// by us. The previously used regex was bad because it was significantly slower
			// and could easily hit the backtrace limit for larger inputs
			$openingTag = mb_strpos($highlightedCode, '&lt;?php&nbsp;');
			$closingTag = mb_strrpos($highlightedCode, '?&gt;');
			$tmp = mb_substr($highlightedCode, 0, $openingTag);
			$tmp .= mb_substr($highlightedCode, $openingTag + 14, $closingTag - $openingTag - 14);
			$tmp .= mb_substr($highlightedCode, $closingTag + 5);
			
			$highlightedCode = $tmp;
		}
		
		// remove breaks
		$highlightedCode = str_replace("\n", "", $highlightedCode);
		$highlightedCode = str_replace('<br />', "\n", $highlightedCode);
		$highlightedCode = str_replace('<br>', "\n", $highlightedCode);
		
		// get tabs back
		$highlightedCode = str_replace('&nbsp;&nbsp;&nbsp;&nbsp;', "\t", $highlightedCode);
		// replace non breaking space with normal space, white-space is preserved by CSS
		$highlightedCode = str_replace('&nbsp;', " ", $highlightedCode);
		
		// convert colors to classes
		$highlightedCode = strtr($highlightedCode, self::$colorToClass);
		
		// replace double quotes by entity
		return Regex::compile('(?<!\<span class=)"(?!\>)')->replace($highlightedCode, '&quot;');
	}
}
