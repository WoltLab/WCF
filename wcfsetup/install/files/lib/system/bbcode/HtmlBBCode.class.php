<?php
namespace wcf\system\bbcode;
use wcf\util\StringUtil;

/**
 * Parses the [html] bbcode tag.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode
 */
class HtmlBBCode extends AbstractBBCode {
	/**
	 * @inheritDoc
	 */
	public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser) {
		$email = '';
		if (isset($openingTag['attributes'][0])) {
			$email = $openingTag['attributes'][0];
		}
		$email = StringUtil::decodeHTML($email);
		
		return '<a href="mailto:' . StringUtil::encodeAllChars($email) . '">' . StringUtil::encodeHTML($email) . '</a>';
	}
}
