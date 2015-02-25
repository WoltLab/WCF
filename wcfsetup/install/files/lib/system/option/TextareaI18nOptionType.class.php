<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * Option type implementation for textareas with i18n support.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category	Community Framework
 */
class TextareaI18nOptionType extends TextareaOptionType {
	/**
	 * @see	\wcf\system\option\AbstractOptionType::$supportI18n
	 */
	protected $supportI18n = true;
	
	/**
	 * @see	\wcf\system\option\IOptionType::getFormElement()
	 */
	public function getFormElement(Option $option, $value) {
		I18nHandler::getInstance()->assignVariables(!empty($_POST));
		
		WCF::getTPL()->assign(array(
			'option' => $option,
			'value' => $value
		));
		return WCF::getTPL()->fetch('textareaI18nOptionType');
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::validate()
	 */
	public function validate(Option $option, $newValue) {
		if (!I18nHandler::getInstance()->validateValue($option->optionName, $option->requireI18n, true)) {
			throw new UserInputException($option->optionName, 'validationFailed');
		}
	}
}
