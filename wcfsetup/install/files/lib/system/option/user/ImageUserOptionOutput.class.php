<?php
namespace wcf\system\option\user;
use wcf\data\user\option\UserOption;
use wcf\data\user\User;
use wcf\util\StringUtil;

/**
 * User option output implementation for an image.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.user
 * @category	Community Framework
 */
class ImageUserOptionOutput implements IUserOptionOutput {
	/**
	 * @see	\wcf\system\option\user\IUserOptionOutput::getOutput()
	 */
	public function getOutput(User $user, UserOption $option, $value) {
		if (empty($value)) return '';
		
		return '<img src="'.StringUtil::encodeHTML($value).'" alt="" />';
	}
}
