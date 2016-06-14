<?php
namespace wcf\system\captcha;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles captchas.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Captcha
 */
class CaptchaHandler extends SingletonFactory {
	/**
	 * available captcha object types
	 * @var	ObjectType[]
	 */
	protected $objectTypes = [];
	
	/**
	 * Returns the available captcha types for selection.
	 * 
	 * @return	string[]
	 */
	public function getCaptchaSelection() {
		$selection = [];
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
	 * @return	ObjectType|null
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
	 * @return	ObjectType|null
	 */
	public function getObjectTypeByName($objectType) {
		return ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.captcha', $objectType);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		$objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.captcha');
		foreach ($objectTypes as $objectType) {
			$this->objectTypes[$objectType->objectTypeID] = $objectType;
		}
	}
}
