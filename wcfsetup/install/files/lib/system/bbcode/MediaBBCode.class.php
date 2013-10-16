<?php
namespace wcf\system\bbcode;
use wcf\data\bbcode\media\provider\BBCodeMediaProvider;
use wcf\util\StringUtil;

/**
 * Parses the [media] bbcode tag.
 * 
 * @author	Tim Duesterhus
 * @copyright	2011-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bbcode
 * @category	Community Framework
 */
class MediaBBCode extends AbstractBBCode {
	/**
	 * @see	\wcf\system\bbcode\IBBCode::getParsedTag()
	 */
	public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser) {
		$content = StringUtil::trim($content);
		
		if ($parser->getOutputType() == 'text/html') {
			foreach (BBCodeMediaProvider::getCache() as $provider) {
				if ($provider->matches($content)) {
					return $provider->getOutput($content);
				}
			}
		}
		if ($parser->getOutputType() == 'text/simplified-html') {
			foreach (BBCodeMediaProvider::getCache() as $provider) {
				if ($provider->matches($content)) {
					return StringUtil::getAnchorTag(StringUtil::decodeHTML($content));
				}
			}
		}
		
		return $content;
	}
}
