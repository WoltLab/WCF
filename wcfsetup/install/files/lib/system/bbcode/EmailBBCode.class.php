<?php
namespace wcf\system\bbcode;
use wcf\util\StringUtil;

/**
 * Parses the [email] bbcode tag.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bbcode
 * @category	Community Framework
 */
class EmailBBCode extends AbstractBBCode {
	/**
	 * @see	\wcf\system\bbcode\IBBCode::getParsedTag()
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
