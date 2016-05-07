<?php
namespace wcf\form;
use wcf\system\request\LinkHandler;
use wcf\system\user\authentication\UserAuthenticationFactory;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Shows the user login form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	form
 * @category	Community Framework
 */
class LoginForm extends \wcf\acp\form\LoginForm {
	const AVAILABLE_DURING_OFFLINE_MODE = true;
	
	/**
	 * @inheritDoc
	 */
	public $enableTracking = true;
	
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
		
		// change user
		WCF::getSession()->changeUser($this->user);
		
		// get redirect url
		$this->checkURL();
		$this->saved();
		
		// redirect to url
		WCF::getTPL()->assign('__hideUserMenu', true);
		HeaderUtil::redirect($this->url);
		exit;
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'useCookies' => $this->useCookies,
			'supportsPersistentLogins' => UserAuthenticationFactory::getInstance()->getUserAuthentication()->supportsPersistentLogins(),
			'loginController' => LinkHandler::getInstance()->getLink('Login')
		]);
	}
	
	/**
	 * Gets the redirect url.
	 */
	protected function checkURL() {
		if (empty($this->url) || mb_stripos($this->url, '?Login/') !== false) {
			$this->url = LinkHandler::getInstance()->getLink();
		}
		
		// drop index.php
		if (!URL_LEGACY_MODE) {
			$this->url = preg_replace('~index\.php~', '', $this->url);
		}
	}
}
