<?php
namespace wcf\system\user\authentication;
use wcf\system\event\EventHandler;

/**
 * Gets the user authentication instance.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.authentication
 * @category 	Community Framework
 */
class UserAuthenticationFactory {
	/**
	 * user authentication instance
	 * @var wcf\system\user\authentication\IUserAuthentication
	 */
	protected static $userAuthentication = null;
	
	/**
	 * Returns user authentication instance.
	 * 
	 * @return wcf\system\user\authentication\IUserAuthentication
	 */
	public static function getUserAuthentication() {
		if (static::$userAuthentication === null) {
			// call loadInstance event
			EventHandler::getInstance()->fireAction(__CLASS__, 'loadUserAuthentication');
			
			// get default implementation
			static::loadUserAuthentication();
		}
		
		return static::$userAuthentication;
	}
	
	/**
	 * Loads the user authentication .
	 */
	protected static function loadUserAuthentication() {
		static::$userAuthentication = DefaultUserAuthentication::getInstance();
	}
}
