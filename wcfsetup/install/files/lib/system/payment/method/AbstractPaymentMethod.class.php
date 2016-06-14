<?php
namespace wcf\system\payment\method;

/**
 * Abstract implementation of a payment method.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Payment\Method
 */
abstract class AbstractPaymentMethod implements IPaymentMethod {
	/**
	 * @inheritDoc
	 */
	public function supportsRecurringPayments() {
		return false;
	}
}
