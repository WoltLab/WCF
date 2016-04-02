<?php
namespace wcf\system\user\online\location;
use wcf\data\page\PageCache;
use wcf\data\user\online\UserOnline;
use wcf\system\page\handler\IOnlineLocationPageHandler;
use wcf\system\SingletonFactory;

/**
 * Handles user online locations.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.online.location
 * @category	Community Framework
 * @deprecated	since 2.2
 */
class UserOnlineLocationHandler extends SingletonFactory {
	/**
	 * Returns the location of the given user.
	 * 
	 * @param	UserOnline	$user
	 * @return	string
	 */
	public function getLocation(UserOnline $user) {
		$oldLocation = $user->getLocation();
		$user->setLocation();
		$newLocation = $user->getLocation();
		$user->setLocation($oldLocation);
		
		return $newLocation;
	}
}
