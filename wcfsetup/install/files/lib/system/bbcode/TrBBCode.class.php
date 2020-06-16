<?php
namespace wcf\system\bbcode;

/**
 * Parses the [tr] bbcode tag.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode
 */
class TrBBCode extends AbstractBBCode {
	/**
	 * @inheritDoc
	 */
	public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser) {
		// ignore these tags as they occur outside of a table
		return '[tr]' . $content . '[/tr]';
	}
}
