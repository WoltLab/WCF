<?php
namespace wcf\system\user\authentication;
use wcf\system\event\EventHandler;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\util\ClassUtil;

/**
 * Gets the user authentication instance.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.authentication
 * @category	Community Framework
 */
class UserAuthenticationFactory extends SingletonFactory {
	/**
	 * user authentication class name
	 * @var	string
	 */
	public $className = 'wcf\system\user\authentication\DefaultUserAuthentication';
	
	/**
	 * user authentication instance
	 * @var	\wcf\system\user\authentication\IUserAuthentication
	 */
	protected $userAuthentication = null;
	
	/**
	 * @see	\wcf\system\SingletonFactory
	 */
	protected function init() {
		// call loadInstance event
		EventHandler::getInstance()->fireAction($this, 'init');
		
		if (!ClassUtil::isInstanceOf($this->className, 'wcf\system\user\authentication\IUserAuthentication')) {
			throw new SystemException("'" . $this->className . "' does not implement 'wcf\system\user\authentication\IUserAuthentication'");
		}
		
		$this->userAuthentication = call_user_func(array($this->className, 'getInstance'));
	}
	
	/**
	 * Returns user authentication instance.
	 * 
	 * @return	\wcf\system\user\authentication\IUserAuthentication
	 */
	public function getUserAuthentication() {
		return $this->userAuthentication;
	}
}
