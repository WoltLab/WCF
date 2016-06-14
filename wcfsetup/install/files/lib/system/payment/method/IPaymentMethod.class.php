<?php
namespace wcf\system\payment\method;

/**
 * Default interface for payment methods.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Payment\Method
 */
interface IPaymentMethod {
	/**
	 * Returns true, if this payment method supports recurring payments.
	 * 
	 * @return	boolean
	 */
	public function supportsRecurringPayments();
	
	/**
	 * Returns a list of supported currencies.
	 * 
	 * @return	string[]
	 */
	public function getSupportedCurrencies();
	
	/**
	 * Returns the HTML code of the purchase button.
	 * 
	 * @param	float		$cost
	 * @param	string		$currency	ISO 4217 code
	 * @param	string		$name		product/item name
	 * @param	string		$token		custom token
	 * @param	string		$returnURL
	 * @param	string		$cancelReturnURL
	 * @param	boolean		$isRecurring
	 * @param	integer		$subscriptionLength
	 * @param	string		$subscriptionLengthUnit
	 * 
	 * @return	string
	 */
	public function getPurchaseButton($cost, $currency, $name, $token, $returnURL, $cancelReturnURL, $isRecurring = false, $subscriptionLength = 0, $subscriptionLengthUnit = '');
}
