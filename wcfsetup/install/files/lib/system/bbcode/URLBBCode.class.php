<?php
namespace wcf\system\bbcode;
use wcf\util\StringUtil;

/**
 * Parses the [url] bbcode tag.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode
 */
class URLBBCode extends AbstractBBCode {
	/**
	 * @inheritDoc
	 */
	public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser) {
		$url = '';
		if (isset($openingTag['attributes'][0])) {
			$url = $openingTag['attributes'][0];
		}
		
		$noTitle = ($content == $url);
		$url = StringUtil::decodeHTML($url);
		
		// add protocol if necessary
		if (!preg_match("/[a-z]:\/\//si", $url)) $url = 'http://'.$url;
		
		return StringUtil::getAnchorTag($url, (!$noTitle ? $content : ''), false);
	}
}
