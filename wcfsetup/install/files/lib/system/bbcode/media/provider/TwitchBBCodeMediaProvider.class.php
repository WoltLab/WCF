<?php
namespace wcf\system\bbcode\media\provider;
use wcf\system\application\ApplicationHandler;

/**
 * Media provider callback for Twitch urls.
 *
 * @author	Marcel Werk
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode\Media\Provider
 * @since	3.1
 */
class TwitchBBCodeMediaProvider implements IBBCodeMediaProvider {
	/**
	 * @var string
	 */
	private static $parent;
	
	/**
	 * @inheritDoc
	 */
	public function parse($url, array $matches = []) {
		$src = '';
		if (!empty($matches['CLIP'])) {
			$src = 'https://clips.twitch.tv/embed?clip=' . $matches['CLIP'];
		}
		
		if (!empty($matches['CHANNEL'])) {
			$src = 'https://player.twitch.tv/?channel=' . $matches['CHANNEL'];
		}
		
		if (!empty($matches['VIDEO'])) {
			$src = 'https://player.twitch.tv/?video=' . $matches['VIDEO'];
		}
		
		if (!empty($src)) {
			return '<div class="videoContainer"><iframe src="' . $src . '&parent=' . self::getParent() . '&autoplay=false" allowfullscreen></iframe></div>';
		}
		
		return '';
	}
	
	/**
	 * @return string
	 */
	private static function getParent() {
		if (self::$parent === null) {
			self::$parent = parse_url(ApplicationHandler::getInstance()->getActiveApplication()->getPageURL())['host'];
		}
		
		return self::$parent;
	}
}
