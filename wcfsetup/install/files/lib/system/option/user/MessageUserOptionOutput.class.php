<?php
namespace wcf\system\option\user;
use wcf\data\user\option\UserOption;
use wcf\data\user\User;
use wcf\system\bbcode\MessageParser;
use wcf\system\WCF;

/**
 * User option output implementation for a formatted textarea value.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user
 * @subpackage	system.option.user
 * @category	Community Framework
 */
class MessageUserOptionOutput implements IUserOptionOutput {
	/**
	 * @see	wcf\system\option\user\IUserOptionOutput::getOutput()
	 */
	public function getOutput(User $user, UserOption $option, $value) {
		MessageParser::getInstance()->setOutputType('text/html');
		
		WCF::getTPL()->assign(array(
			'option' => $option,
			'value' => MessageParser::getInstance()->parse($value),
		));
		return WCF::getTPL()->fetch('messageUserOptionOutput');
	}
}
