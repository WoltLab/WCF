<?php
namespace wcf\system\auth;
use wcf\data\user\User;
use wcf\system\event\EventHandler;
use wcf\system\SingletonFactory;

/**
 * All user authentication types should implement the abstract functions of this class.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.auth
 * @category 	Community Framework
 */
abstract class UserAuth extends SingletonFactory {
	/**
	 * @see	wcf\system\SingletonFactory::prepareInitialization()
	 */
	protected static function prepareInitialization($className) {
		// call loadInstance event
		EventHandler::getInstance()->fireAction(__CLASS__, 'loadInstance');

		return 'wcf\system\auth\UserAuthDefault';
	}
	
	/**
	 * Returns true, if this auth type supports persistent logins.
	 * 
	 * @return	boolean
	 */
	public abstract function supportsPersistentLogins();
	
	/**
	 * Stores the user access data for a persistent login.
	 * 
	 * @param	User		$user
	 * @param 	string		$username
	 * @param	string		$password
	 */
	public abstract function storeAccessData(User $user, $username, $password);
	
	/**
	 * Does an manual user login.
	 * 
	 * @param 	string		$username
	 * @param	string		$password
	 * @param	string		$userClassname		class name of user class
	 * @return	User
	 */
	public abstract function loginManually($username, $password, $userClassname = 'wcf\data\user\User');
	
	/**
	 * Does an automatic user login.
	 * 
	 * @param	boolean		$persistent		true = persistent login
	 * @param	string		$userClassname		class name of user class
	 * @return	User
	 */
	public abstract function loginAutomatically($persistent = false, $userClassname = 'wcf\data\user\User');
}
