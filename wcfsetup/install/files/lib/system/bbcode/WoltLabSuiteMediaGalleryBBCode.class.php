<?php
namespace wcf\system\bbcode;

/**
 * Parses the [wsmg] bbcode tag.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode
 */
class WoltLabSuiteMediaGalleryBBCode extends AbstractBBCode {
	/**
	 * @inheritDoc
	 */
	public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser) {
		return '<p>TODO: WoltLabSuiteMediaGalleryBBCode</p>';
	}
}
