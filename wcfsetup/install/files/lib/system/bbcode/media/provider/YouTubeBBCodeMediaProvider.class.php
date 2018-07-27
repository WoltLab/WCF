<?php
namespace wcf\system\bbcode\media\provider;

/**
 * Media provider callback for YouTube urls.
 *
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode\Media\Provider
 * @since	3.1
 */
class YouTubeBBCodeMediaProvider implements IBBCodeMediaProvider {
	/**
	 * @inheritDoc
	 */
	public function parse($url, array $matches = []) {
		$start = 0;
		if (!empty($matches['start'])) {
			if (preg_match('~^(?:(?:(?P<h>\d+)h)?(?P<m>\d+)m(?P<s>\d+))|(?P<t>\d+)~', $matches['start'], $match)) {
				if (!empty($match['h'])) {
					$start += intval($match['h']) * 3600;
				}
				if (!empty($match['m'])) {
					$start += intval($match['m']) * 60;
				}
				if (!empty($match['s'])) {
					$start += intval($match['s']);
				}
				if (!empty($match['t'])) {
					$start += intval($match['t']);
				}
			}
		}
		
		return '<div class="videoContainer"><iframe src="https://www.youtube-nocookie.com/embed/' . $matches['ID'] . '?wmode=transparent' . ($start ? '&amp;start='.$start : '') . '" allowfullscreen></iframe></div>';
	}
}
