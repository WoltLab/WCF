<?php
namespace wcf\system\bbcode;
use wcf\util\StringUtil;

/**
 * Parses the [email] bbcode tag.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode
 */
class EmailBBCode extends AbstractBBCode {
	/**
	 * @inheritDoc
	 */
	public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser) {
		$email = '';
		if (isset($openingTag['attributes'][0])) {
			$email = $openingTag['attributes'][0];
		}
		$email = StringUtil::decodeHTML($email);
		
		return '<a href="mailto:' . StringUtil::encodeAllChars($email) . '">' . $content . '</a>';
	}
}
