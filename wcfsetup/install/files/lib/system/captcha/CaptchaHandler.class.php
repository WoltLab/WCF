<?php
namespace wcf\system\captcha;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles captchas.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.captcha
 * @category	Community Framework
 */
class CaptchaHandler extends SingletonFactory {
	/**
	 * Returns the available captcha types for selection.
	 * 
	 * @return	array<string>
	 */
	public function getCaptchaSelection() {
		$selection = array();
		foreach ($this->objectTypes as $objectType) {
			if ($objectType->getProcessor()->isAvailable()) {
				$selection[$objectType->objectType] = WCF::getLanguage()->get('wcf.captcha.'.$objectType->objectType);
			}
		}
		
		return $selection;
	}
	
	/**
	 * Returns the captcha object type with the given id or null if no such
	 * object type exists.
	 * 
	 * @param	integer		$objectTypeID
	 * @return	\wcf\data\object\type\ObjectType
	 */
	public function getObjectType($objectTypeID) {
		if (isset($this->objectTypes[$objectTypeID])) {
			return $this->objectTypes[$objectTypeID];
		}
		
		return null;
	}
	
	/**
	 * Returns the captcha object type with the given name or null if no such
	 * object type exists.
	 * 
	 * @param	string		$objectType
	 * @return	\wcf\data\object\type\ObjectType
	 */
	public function getObjectTypeByName($objectType) {
		return ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.captcha', $objectType);
	}
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.captcha');
		foreach ($objectTypes as $objectType) {
			$this->objectTypes[$objectType->objectTypeID] = $objectType;
		}
	}
}
