<?php
namespace wcf\system\bbcode;
use wcf\util\StringUtil;

/**
 * Parses the [url] bbcode tag.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bbcode
 * @category	Community Framework
 */
class URLBBCode extends AbstractBBCode {
	/**
	 * @see	\wcf\system\bbcode\IBBCode::getParsedTag()
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
