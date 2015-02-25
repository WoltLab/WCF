<?php
namespace wcf\system\bbcode\highlighter;
use wcf\system\Regex;

/**
 * Highlights syntax of template documents with smarty-syntax.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bbcode.highlighter
 * @category	Community Framework
 */
class TplHighlighter extends HtmlHighlighter {
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::highlightComments()
	 */
	protected function highlightComments($string) {
		$string = parent::highlightComments($string);
		
		// highlight template tags
		$string = Regex::compile('\{(?=\S).+?(?<=\S)\}', Regex::DOT_ALL)->replace($string, '<span class="hlKeywords3">\\0</span>');
		
		return $string;
	}
}
