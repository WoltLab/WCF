<?php
namespace wcf\system\payment\method;

/**
 * IPaymentMethod implementation for SofortUeberweisung.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Payment\Method
 */
class SofortUeberweisungPaymentMethod extends AbstractPaymentMethod {
	/**
	 * @inheritDoc
	 */
	public function getSupportedCurrencies() {
		return [
			'EUR' // Euro
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public function getPurchaseButton($cost, $currency, $name, $token, $returnURL, $cancelReturnURL, $isRecurring = false, $subscriptionLength = 0, $subscriptionLengthUnit = '') {
		// @todo
	}
}
