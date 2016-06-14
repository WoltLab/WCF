<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\exception\UserInputException;
use wcf\system\payment\method\PaymentMethodHandler;
use wcf\system\WCF;

/**
 * Option type implementation for selecting payment methods.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option
 */
class PaymentMethodSelectOptionType extends AbstractOptionType {
	/**
	 * @inheritDoc
	 */
	public function getFormElement(Option $option, $value) {
		$selectOptions = PaymentMethodHandler::getInstance()->getPaymentMethodSelection();
		
		return WCF::getTPL()->fetch('paymentMethodSelectOptionType', 'wcf', [
			'selectOptions' => $selectOptions,
			'option' => $option,
			'value' => explode(',', $value)
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate(Option $option, $newValue) {
		if (!is_array($newValue)) {
			$newValue = [];
		}
		
		$selectOptions = PaymentMethodHandler::getInstance()->getPaymentMethodSelection();
		foreach ($newValue as $optionName) {
			if (!isset($selectOptions[$optionName])) {
				throw new UserInputException($option->optionName, 'validationFailed');
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getData(Option $option, $newValue) {
		if (!is_array($newValue)) return '';
		return implode(',', $newValue);
	}
}
