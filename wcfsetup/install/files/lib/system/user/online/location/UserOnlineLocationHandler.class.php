<?php
namespace wcf\system\user\online\location;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\online\UserOnline;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles user online locations.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.online.location
 * @category	Community Framework
 */
class UserOnlineLocationHandler extends SingletonFactory {
	/**
	 * page locations
	 * @var	array
	 */
	public $locations = array();
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		// load locations
		foreach (ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.user.online.location') as $objectType) {
			$this->locations[$objectType->controller] = $objectType;
		}
	}
	
	/**
	 * Gets the location of a given user.
	 * 
	 * @param	\wcf\data\user\online\UserOnline		$user
	 * @return	string
	 */
	public function getLocation(UserOnline $user) {
		if (isset($this->locations[$user->controller])) {
			if ($this->locations[$user->controller]->getProcessor()) {
				$this->locations[$user->controller]->getProcessor()->cache($user);
				return $this->locations[$user->controller]->getProcessor()->get($user, $this->locations[$user->controller]->languagevariable);
			}
			else {
				return WCF::getLanguage()->get($this->locations[$user->controller]->languagevariable);
			}
		}
		
		return '';
	}
}
