<?php
namespace wcf\system\bbcode;
use wcf\system\WCF;

/**
 * Parses the [spoiler] bbcode tag.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bbcode
 * @category	Community Framework
 */
class SpoilerBBCode extends AbstractBBCode {
	/**
	 * @see	\wcf\system\bbcode\IBBCode::getParsedTag()
	 */
	public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser) {
		if ($parser->getOutputType() == 'text/html') {
			WCF::getTPL()->assign(array(
				'content' => $content,
				'buttonTitle' => (!empty($openingTag['attributes'][0]) ? $openingTag['attributes'][0] : '')
			));
			return WCF::getTPL()->fetch('spoilerBBCodeTag');
		}
		if ($parser->getOutputType() == 'text/simplified-html') {
			return WCF::getLanguage()->get('wcf.bbcode.spoiler.text');
		}
	}
}
