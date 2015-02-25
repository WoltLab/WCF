<?php
namespace wcf\system\payment\method;

/**
 * IPaymentMethod implementation for SofortUeberweisung.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.payment.method
 * @category	Community Framework
 */
class SofortUeberweisungPaymentMethod extends AbstractPaymentMethod {
	/**
	 * @see	\wcf\system\payment\method\IPaymentMethod::getSupportedCurrencies()
	 */
	public function getSupportedCurrencies() {
		return array(
			'EUR' // Euro
		);
	}
	
	/**
	 * @see	\wcf\system\payment\method\IPaymentMethod::getPurchaseButton()
	 */
	public function getPurchaseButton($cost, $currency, $name, $token, $returnURL, $cancelReturnURL, $isRecurring = false, $subscriptionLength = 0, $subscriptionLengthUnit = '') {
		// @todo
	}
}
