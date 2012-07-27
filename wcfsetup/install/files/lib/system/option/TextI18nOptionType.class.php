<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * TextI18nOptionType is an implementation of IOptionType for 'input type="text"' tags with i18n support.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category 	Community Framework
 */
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
		
		// nasty workaround for javascript problem with multiple line strings
		$i18nValues = UltimateCore::getTPL()->get('i18nValues');
		foreach ($i18nValues['text'] as $languageID => $value) {
		    $i18nValues['text'][$languageID] = str_replace("\r", '_specialNewline\\', $value);
		}
		
		WCF::getTPL()->assign(array(
			'option' => $option,
			'inputType' => $this->inputType,
			'value' => $value,
			'i18nValues' => $i18nValues
		));
		return WCF::getTPL()->fetch('textI18nOptionType');
	}
	
	/**
	 * @see wcf\system\option\IOptionType::validate()
	 */
	public function validate(Option $option, $newValue) {
		if (!I18nHandler::getInstance()->validateValue($option->optionName, $option->requireI18n, true)) {
			throw new UserInputException($option->optionName, 'validationFailed');
		}
	}
}
