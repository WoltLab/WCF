<?php
namespace wcf\system\bbcode;
use wcf\data\bbcode\media\provider\BBCodeMediaProvider;
use wcf\util\StringUtil;

/**
 * Parses the [media] bbcode tag.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode
 */
class MediaBBCode extends AbstractBBCode {
	/**
	 * @inheritDoc
	 */
	public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser) {
		$content = StringUtil::trim($openingTag['attributes'][0]);
		
		/** @var HtmlBBCodeParser $parser */
		if ($parser->getOutputType() == 'text/html') {
			foreach (BBCodeMediaProvider::getCache() as $provider) {
				if ($provider->matches($content)) {
					if ($parser instanceof HtmlBBCodeParser && $parser->getIsGoogleAmp()) {
						return StringUtil::getAnchorTag($content, '', true, true);
					}
					else {
						return $provider->getOutput($content);
					}
				}
			}
		}
		else if ($parser->getOutputType() == 'text/simplified-html' && !$parser->getRemoveLinks()) {
			foreach (BBCodeMediaProvider::getCache() as $provider) {
				if ($provider->matches($content)) {
					return StringUtil::getAnchorTag($content, '', true, true);
				}
			}
		}
		
		return StringUtil::encodeHTML($content);
	}
}
