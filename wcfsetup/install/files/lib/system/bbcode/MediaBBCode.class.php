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
 * @package	WoltLabSuite\Core\System\Bbcode
 */
class MediaBBCode extends AbstractBBCode {
	/**
	 * @inheritDoc
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
					return StringUtil::getAnchorTag($content);
				}
			}
		}
		
		return StringUtil::encodeHTML($content);
	}
}
