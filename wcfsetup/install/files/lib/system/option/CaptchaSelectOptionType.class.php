<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\captcha\CaptchaHandler;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Option type implementation for selecting a captcha type.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option
 */
class CaptchaSelectOptionType extends AbstractOptionType {
	/**
	 * @inheritDoc
	 */
	public function getFormElement(Option $option, $value) {
		$selectOptions = CaptchaHandler::getInstance()->getCaptchaSelection();
		if ($option->allowemptyvalue) {
			$selectOptions = array_merge(
				[
					'' => WCF::getLanguage()->get('wcf.captcha.useNoCaptcha')
				],
				$selectOptions
			);
		}
		
		return WCF::getTPL()->fetch('selectOptionType', 'wcf', [
			'selectOptions' => $selectOptions,
			'option' => $option,
			'value' => $value
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate(Option $option, $newValue) {
		if (!$newValue) return;
		
		$selection = CaptchaHandler::getInstance()->getCaptchaSelection();
		if (!isset($selection[$newValue])) {
			throw new UserInputException($option->optionName);
		}
	}
}
