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
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category	Community Framework
 */
class PaymentMethodSelectOptionType extends AbstractOptionType {
	/**
	 * @see	\wcf\system\option\IOptionType::getFormElement()
	 */
	public function getFormElement(Option $option, $value) {
		$selectOptions = PaymentMethodHandler::getInstance()->getPaymentMethodSelection();
		
		return WCF::getTPL()->fetch('paymentMethodSelectOptionType', 'wcf', array(
			'selectOptions' => $selectOptions,
			'option' => $option,
			'value' => explode(',', $value)
		));
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::validate()
	 */
	public function validate(Option $option, $newValue) {
		if (!is_array($newValue)) {
			$newValue = array();
		}
		
		$selectOptions = PaymentMethodHandler::getInstance()->getPaymentMethodSelection();
		foreach ($newValue as $optionName) {
			if (!isset($selectOptions[$optionName])) {
				throw new UserInputException($option->optionName, 'validationFailed');
			}
		}
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::getData()
	 */
	public function getData(Option $option, $newValue) {
		if (!is_array($newValue)) return '';
		return implode(',', $newValue);
	}
}
