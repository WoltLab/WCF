<?php
namespace wcf\system\option\user;
use wcf\data\user\option\UserOption;
use wcf\data\user\User;
use wcf\util\StringUtil;

/**
 * User option output implementation for the output of an url.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option\User
 */
class URLUserOptionOutput implements IUserOptionOutput {
	/**
	 * @inheritDoc
	 */
	public function getOutput(User $user, UserOption $option, $value) {
		if (empty($value) || $value == 'http://') return '';
		
		$value = self::getURL($value);
		
		return StringUtil::getAnchorTag($value, $value, true, true);
	}
	
	/**
	 * Formats the URL.
	 * 
	 * @param	string		$url
	 * @return	string
	 */
	private static function getURL($url) {
		if (!preg_match('~^https?://~i', $url)) {
			$url = 'http://'.$url;
		}
		
		return $url;
	}
}
