<?php
namespace wcf\system\payment\method;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles enabled/available payment methods.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.payment.method
 * @category	Community Framework
 */
class PaymentMethodHandler extends SingletonFactory {
	/**
	 * payment methods
	 * @var	array
	 */
	protected $paymentMethods = [];
	
	/**
	 * payment method object types
	 * @var	array
	 */
	protected $objectTypes = [];
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$availablePaymentMethods = explode(',', AVAILABLE_PAYMENT_METHODS);
		$this->objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.payment.method');
		foreach ($this->objectTypes as $objectType) {
			if (in_array($objectType->objectType, $availablePaymentMethods)) {
				$this->paymentMethods[] = $objectType->getProcessor();
			}
		}
	}
	
	/**
	 * Returns the available payment methods.
	 * 
	 * @return	array
	 */
	public function getPaymentMethods() {
		return $this->paymentMethods;
	}
	
	/**
	 * Returns the available payment methods for selection.
	 * 
	 * @return	string[]
	 */
	public function getPaymentMethodSelection() {
		$selection = [];
		foreach ($this->objectTypes as $objectType) {
			$selection[$objectType->objectType] = WCF::getLanguage()->get('wcf.payment.'.$objectType->objectType);
		}
		
		return $selection;
	}
}
