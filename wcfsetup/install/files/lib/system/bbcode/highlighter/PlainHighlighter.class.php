<?php
namespace wcf\system\bbcode\highlighter;
use wcf\util\StringUtil;

/**
 * Does no highlighting.
 * 
 * @author	Tim Duesterhus
 * @copyright	2011 Tim Duesterhus
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode\Highlighter
 */
class PlainHighlighter extends Highlighter {
	/**
	 * @inheritDoc
	 */
	public function highlight($code) {
		return StringUtil::encodeHTML($code);
	}
}
