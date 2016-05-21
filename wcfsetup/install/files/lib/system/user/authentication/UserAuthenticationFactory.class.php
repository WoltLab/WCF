<?php
namespace wcf\system\user\authentication;
use wcf\system\event\EventHandler;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;

/**
 * Gets the user authentication instance.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
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
	public $className = DefaultUserAuthentication::class;
	
	/**
	 * user authentication instance
	 * @var	\wcf\system\user\authentication\IUserAuthentication
	 */
	protected $userAuthentication = null;
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		// call loadInstance event
		EventHandler::getInstance()->fireAction($this, 'init');
		
		if (!is_subclass_of($this->className, IUserAuthentication::class)) {
			throw new SystemException("'" . $this->className . "' does not implement '".IUserAuthentication::class."'");
		}
		
		$this->userAuthentication = call_user_func([$this->className, 'getInstance']);
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
