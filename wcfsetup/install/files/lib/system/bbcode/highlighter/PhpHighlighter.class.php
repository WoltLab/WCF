<?php
namespace wcf\system\bbcode\highlighter;
use wcf\system\Regex;

/**
 * Highlights syntax of PHP sourcecode.
 * 
 * @author	Tim Duesterhus, Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bbcode.highlighter
 * @category	Community Framework
 */
class PhpHighlighter extends Highlighter {
	public static $colorToClass = array();
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		parent::init();
		
		$types = array('default' => 'hlKeywords1', 'keyword' => 'hlKeywords2', 'comment' => 'hlComments', 'string' => 'hlQuotes');
		
		self::$colorToClass['<span style="color: '.ini_get('highlight.html').'">'] = '<span>';
		foreach ($types as $type => $class) {
			self::$colorToClass['<span style="color: '.ini_get('highlight.'.$type).'">'] = '<span class="'.$class.'">';
		}
	}
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::highlight()
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
			$regex = new Regex('([^\\2]*)(&lt;\?php&nbsp;)(.*)(&nbsp;.*\?&gt;)([^\\4]*)', Regex::CASE_INSENSITIVE | Regex::DOT_ALL);
			$highlightedCode = $regex->replace($highlightedCode, '\\1\\3\\5');
		}
		
		// remove breaks
		$highlightedCode = str_replace("\n", "", $highlightedCode);
		$highlightedCode = str_replace('<br />', "\n", $highlightedCode);
		
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
