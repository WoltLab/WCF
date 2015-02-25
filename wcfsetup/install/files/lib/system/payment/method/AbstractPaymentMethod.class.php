<?php
namespace wcf\system\payment\method;

/**
 * Abstract implementation of a payment method.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.payment.method
 * @category	Community Framework
 */
abstract class AbstractPaymentMethod implements IPaymentMethod {
	/**
	 * @see	\wcf\system\payment\method\IPaymentMethod::supportsRecurringPayments()
	 */
	public function supportsRecurringPayments() {
		return false;
	}
}
