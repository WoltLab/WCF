<?php
namespace wcf\system\payment\method;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles enabled/available payment methods.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Payment\Method
 */
class PaymentMethodHandler extends SingletonFactory {
	/**
	 * payment methods
	 * @var	IPaymentMethod[]
	 */
	protected $paymentMethods = [];
	
	/**
	 * payment method object types
	 * @var	ObjectType[]
	 */
	protected $objectTypes = [];
	
	/**
	 * @inheritDoc
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
	 * @return	IPaymentMethod[]
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
