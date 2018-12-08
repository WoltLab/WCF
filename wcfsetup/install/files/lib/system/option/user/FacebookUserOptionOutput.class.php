<?php
namespace wcf\system\option\user;
use wcf\data\user\option\UserOption;
use wcf\data\user\User;
use wcf\util\StringUtil;

/**
 * User option output implementation for the output of a facebook user profile.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option\User
 */
class FacebookUserOptionOutput implements IUserOptionOutput {
	/**
	 * @inheritDoc
	 */
	public function getOutput(User $user, UserOption $option, $value) {
		if (empty($value)) return '';
		
		$url = StringUtil::encodeHTML('https://www.facebook.com/'.$value);
		$value = StringUtil::encodeHTML($value);
		
		return '<a href="'.$url.'" class="externalURL"'.((EXTERNAL_LINK_REL_NOFOLLOW || EXTERNAL_LINK_TARGET_BLANK) ? (' rel="'.(EXTERNAL_LINK_REL_NOFOLLOW ? 'nofollow' : '').((EXTERNAL_LINK_REL_NOFOLLOW && EXTERNAL_LINK_TARGET_BLANK) ? ' ' : '').(EXTERNAL_LINK_TARGET_BLANK ? 'noopener noreferrer' : '').'"') : '').(EXTERNAL_LINK_TARGET_BLANK ? ' target="_blank"' : '').'>'.$value.'</a>';
	}
}
