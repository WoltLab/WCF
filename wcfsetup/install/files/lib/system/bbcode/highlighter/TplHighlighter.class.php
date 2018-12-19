<?php
namespace wcf\system\bbcode\highlighter;
use wcf\system\Regex;

/**
 * Highlights syntax of template documents with smarty-syntax.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode\Highlighter
 * @deprecated	since 3.2, use Prism to highlight your code.
 */
class TplHighlighter extends HtmlHighlighter {
	/**
	 * @inheritDoc
	 */
	protected function highlightComments($string) {
		$string = parent::highlightComments($string);
		
		// highlight template tags
		$string = Regex::compile('\{(?=\S).+?(?<=\S)\}', Regex::DOT_ALL)->replace($string, '<span class="hlKeywords3">\\0</span>');
		
		return $string;
	}
}
