<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\bbcode\PreParser;
use wcf\system\exception\UserInputException;
use wcf\system\message\censorship\Censorship;
use wcf\system\WCF;

/**
 * Option type implementation for the 'about me' text field.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category	Community Framework
 */
class AboutMeOptionType extends MessageOptionType {
	/**
	 * @see	\wcf\system\option\IOptionType::validate()
	 */
	public function validate(Option $option, $newValue) {
		parent::validate($option, $newValue);
		
		if (WCF::getSession()->getPermission('user.profile.aboutMeMaxLength') < mb_strlen($newValue)) {
			throw new UserInputException($option->optionName, 'tooLong');
		}
		
		// search for censored words
		if (ENABLE_CENSORSHIP) {
			$result = Censorship::getInstance()->test($newValue);
			if ($result) {
				WCF::getTPL()->assign('censoredWords', $result);
				throw new UserInputException($option->optionName, 'censoredWordsFound');
			}
		}
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::getData()
	 */
	public function getData(Option $option, $newValue) {
		$newValue = parent::getData($option, $newValue);
		
		// run pre-parsing
		return PreParser::getInstance()->parse($newValue);
	}
}
