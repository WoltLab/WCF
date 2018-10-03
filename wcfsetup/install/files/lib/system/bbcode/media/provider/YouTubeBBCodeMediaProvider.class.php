<?php
namespace wcf\system\bbcode\media\provider;
use wcf\util\Url;

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
		$parsedUrl = Url::parse($url);
		parse_str($parsedUrl['query'], $queryString);
		$startParameter = $queryString['t'] ?? $queryString['time_continue'] ?? $queryString['start'] ?? '';
		$endParameter = $queryString['end'] ?? '';

		$start = $this->timeToSeconds($startParameter);
		$end = $this->timeToSeconds($endParameter);
		
		return '<div class="videoContainer"><iframe src="https://www.youtube-nocookie.com/embed/' . $matches['ID'] . '?wmode=transparent' . ($start ? '&amp;start='.$start : '') . ($end ? '&amp;end='.$end : '') . '&amp;rel=0" allowfullscreen></iframe></div>';
	}
	
	/**
	 * Converts the given time parameter into seconds.
	 * 
	 * @param	string	$time
	 * @return	int
	 */
	protected function timeToSeconds($time) {
		$result = 0;
		if (preg_match('~^(?:(?:(?P<h>\d+)h)?(?P<m>\d+)m(?P<s>\d+))|(?P<t>\d+)~', $time, $match)) {
			if (!empty($match['h'])) {
				$result += intval($match['h']) * 3600;
			}
			if (!empty($match['m'])) {
				$result += intval($match['m']) * 60;
			}
			if (!empty($match['s'])) {
				$result += intval($match['s']);
			}
			if (!empty($match['t'])) {
				$result += intval($match['t']);
			}
		}
		
		return $result;
	}
}
