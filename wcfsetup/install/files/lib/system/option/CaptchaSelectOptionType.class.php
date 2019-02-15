<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\captcha\CaptchaHandler;
use wcf\system\captcha\RecaptchaHandler;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Option type implementation for selecting a captcha type.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option
 */
class CaptchaSelectOptionType extends AbstractOptionType {
	/**
	 * @inheritDoc
	 */
	public function getFormElement(Option $option, $value) {
		RecaptchaHandler::$forceIsAvailable = true;
		$selectOptions = CaptchaHandler::getInstance()->getCaptchaSelection();
		/** @noinspection PhpUndefinedFieldInspection */
		if ($option->allowemptyvalue) {
			$selectOptions = array_merge(
				['' => WCF::getLanguage()->get('wcf.captcha.useNoCaptcha')],
				$selectOptions
			);
		}
		
		$options = $this->parseEnableOptions($option);
		
		return WCF::getTPL()->fetch('selectOptionType', 'wcf', [
			'disableOptions' => $options['disableOptions'],
			'enableOptions' => $options['enableOptions'],
			'selectOptions' => $selectOptions,
			'option' => $option,
			'value' => $value
		]);
	}
	
	/**
	 * Prepares JSON-encoded values for disabling or enabling dependent options.
	 *
	 * @param	Option	$option
	 * @return	array
	 * @see	SelectOptionType::parseEnableOptions()
	 */
	protected function parseEnableOptions(Option $option) {
		$disableOptions = $enableOptions = '';
		
		if (!empty($option->enableOptions)) {
			$options = $option->parseMultipleEnableOptions();
			
			foreach ($options as $key => $optionData) {
				$tmp = explode(',', $optionData);
				
				foreach ($tmp as $item) {
					if ($item{0} == '!') {
						if (!empty($disableOptions)) $disableOptions .= ',';
						$disableOptions .= "{ value: '".$key."', option: '".mb_substr($item, 1)."' }";
					}
					else {
						if (!empty($enableOptions)) $enableOptions .= ',';
						$enableOptions .= "{ value: '".$key."', option: '".$item."' }";
					}
				}
			}
		}
		
		return [
			'disableOptions' => $disableOptions,
			'enableOptions' => $enableOptions
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate(Option $option, $newValue) {
		if (!$newValue) return;
		
		RecaptchaHandler::$forceIsAvailable = true;
		$selection = CaptchaHandler::getInstance()->getCaptchaSelection();
		if (!isset($selection[$newValue])) {
			throw new UserInputException($option->optionName);
		}
	}
}
