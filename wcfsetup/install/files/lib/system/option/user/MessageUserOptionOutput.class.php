<?php
namespace wcf\system\option\user;
use wcf\data\user\option\UserOption;
use wcf\data\user\User;
use wcf\system\bbcode\MessageParser;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * User option output implementation for a formatted textarea value.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option\User
 */
class MessageUserOptionOutput implements IUserOptionOutput {
	/**
	 * @inheritDoc
	 */
	public function getOutput(User $user, UserOption $option, $value) {
		$value = StringUtil::trim($value);
		if (empty($value)) {
			return '';
		}
		
		MessageParser::getInstance()->setOutputType('text/html');
		
		WCF::getTPL()->assign([
			'option' => $option,
			'value' => MessageParser::getInstance()->parse($value)
		]);
		return WCF::getTPL()->fetch('messageUserOptionOutput');
	}
}
