<?php
namespace wcf\system\auth;
use wcf\data\user\User;
use wcf\system\event\EventHandler;

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
abstract class UserAuth {
	/**
	 * active instance
	 *
	 * @var	UserAuth
	 */
	protected static $instance = null;
	
	/**
	 * Returns an instance of the enabled user auth class.
	 * 
	 * @return	UserAuth
	 */
	public static function getInstance() {
		if (self::$instance === null) {
			// call loadInstance event
			EventHandler::getInstance()->fireAction(__CLASS__, 'loadInstance');
		
			// use default implementation
			if (self::$instance === null) {
				self::$instance = new UserAuthDefault();
			}
		}
		
		return self::$instance;
	}
	
	/**
	 * Prevents creating an additional instance.
	 */
	protected function __clone() {}
	
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
?>
