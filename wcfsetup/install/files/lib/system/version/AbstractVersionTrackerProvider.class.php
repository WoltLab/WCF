<?php
namespace wcf\system\version;
use wcf\data\object\type\AbstractObjectTypeProvider;
use wcf\data\IVersionTrackerObject;
use wcf\system\WCF;

/**
 * Abstract implementation of an version tracker object type provider.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Version
 * @since	3.1
 */
abstract class AbstractVersionTrackerProvider extends AbstractObjectTypeProvider implements IVersionTrackerProvider {
	/**
	 * the default property that should be used when initiating a diff
	 * @var string
	 */
	public static $defaultProperty = '';
	
	/**
	 * list of property names to their phrase, the order in which properties
	 * appear is significant and is used as display orders when comparing changes
	 * @var string[]
	 */
	public static $propertyLabels = [];
	
	/**
	 * list of properties that should be tracked
	 * @var string[]
	 */
	public static $trackedProperties = [];
	
	/**
	 * internal identifier of the menu item that should be marked as active
	 * @var string
	 */
	public $activeMenuItem = '';
	
	/**
	 * true if content supports i18n
	 * @var boolean
	 */
	public $isI18n = false;
	
	/**
	 * permission name to access stored versions
	 * @var string
	 */
	public $permissionCanAccess = '';
	
	/**
	 * @inheritDoc
	 */
	public function canAccess() {
		return WCF::getSession()->getPermission($this->permissionCanAccess);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getActiveMenuItem() {
		return $this->activeMenuItem;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getDefaultProperty() {
		return static::$defaultProperty;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getPropertyLabel($property) {
		if (isset(static::$propertyLabels[$property])) {
			return WCF::getLanguage()->get(static::$propertyLabels[$property]);
		}
		
		return '(void)';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTrackedProperties() {
		return static::$trackedProperties;
	}
	
	/**
	 * @inheritDoc
	 */
	public function isI18n(IVersionTrackerObject $object) {
		return $this->isI18n;
	}
}
