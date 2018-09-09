<?php
namespace wcf\form;
use wcf\system\request\LinkHandler;
use wcf\system\user\authentication\UserAuthenticationFactory;
use wcf\system\WCF;

/**
 * Shows the user login form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Form
 */
class LoginForm extends \wcf\acp\form\LoginForm {
	const AVAILABLE_DURING_OFFLINE_MODE = true;
	
	/**
	 * true enables the usage of cookies to save login credentials
	 * @var	boolean
	 */
	public $useCookies = 1;
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		$this->useCookies = 0;
		if (isset($_POST['useCookies'])) $this->useCookies = intval($_POST['useCookies']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		// set cookies
		if ($this->useCookies == 1) {
			UserAuthenticationFactory::getInstance()->getUserAuthentication()->storeAccessData($this->user, $this->username, $this->password);
		}
		
		if (FORCE_LOGIN) WCF::getSession()->unregister('__wsc_forceLoginRedirect');
		
		// change user
		WCF::getSession()->changeUser($this->user);
		
		$this->saved();
		
		// redirect to url
		WCF::getTPL()->assign('__hideUserMenu', true);
		
		$this->performRedirect();
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'useCookies' => $this->useCookies,
			'supportsPersistentLogins' => UserAuthenticationFactory::getInstance()->getUserAuthentication()->supportsPersistentLogins(),
			'loginController' => LinkHandler::getInstance()->getLink('Login'),
			'forceLoginRedirect' => (FORCE_LOGIN && WCF::getSession()->getVar('__wsc_forceLoginRedirect') !== null)
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function performRedirect() {
		if (empty($this->url) || mb_stripos($this->url, '?login/') !== false || mb_stripos($this->url, '/login/') !== false) {
			$this->url = LinkHandler::getInstance()->getLink();
		}
		
		parent::performRedirect();
	}
}
