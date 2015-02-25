<?php
namespace wcf\system\option\user;
use wcf\data\user\option\UserOption;
use wcf\data\user\User;
use wcf\util\StringUtil;

/**
 * User option output implementation for the output of a google+ user profile.
 * 
 * @author	Jeffrey Reichardt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.user
 * @category	Community Framework
 */
class GooglePlusUserOptionOutput implements IUserOptionOutput {
	/**
	 * @see	\wcf\system\option\user\IUserOptionOutput::getOutput()
	 */
	public function getOutput(User $user, UserOption $option, $value) {
		if (empty($value)) return '';
		
		$url = StringUtil::encodeHTML('https://plus.google.com/'.$value.'/posts');
		$value = StringUtil::encodeHTML($value);
		
		return '<a href="'.$url.'" class="externalURL"'.(EXTERNAL_LINK_REL_NOFOLLOW ? ' rel="me nofollow"' : ' rel="me"').(EXTERNAL_LINK_TARGET_BLANK ? ' target="_blank"' : '').'>'.$value.'</a>';
	}
}
