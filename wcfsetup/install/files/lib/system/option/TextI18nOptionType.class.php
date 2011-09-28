<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

class TextI18nOptionType extends TextOptionType {
	/**
	 * @see	wcf\system\option\AbstractOptionType::$supportI18n
	 */
	protected $supportI18n = true;
	
	/**
	 * @see wcf\system\option\IOptionType::getFormElement()
	 */
	public function getFormElement(Option $option, $value) {
		$useRequestData = (count($_POST)) ? true : false;
		I18nHandler::getInstance()->assignVariables($useRequestData);
		
		WCF::getTPL()->assign(array(
			'option' => $option,
			'inputType' => $this->inputType,
			'value' => $value
		));
		return WCF::getTPL()->fetch('optionTypeTextI18n');
	}
	
	/**
	 * @see wcf\system\option\IOptionType::validate()
	 */
	public function validate(Option $option, $newValue) {
		if (!I18nHandler::getInstance()->validateValue($option->optionName)) {
			throw new UserInputException($option->optionName, 'validationFailed');
		}
	}
}
