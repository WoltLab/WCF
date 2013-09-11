<?php
namespace wcf\form;
use wcf\system\request\LinkHandler;
use wcf\system\user\authentication\UserAuthenticationFactory;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;
use wcf\util\UserUtil; 

/**
 * Shows the user login form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	form
 * @category	Community Framework
 */
class LoginForm extends \wcf\acp\form\LoginForm {
	const AVAILABLE_DURING_OFFLINE_MODE = true;
	
	/**
	 * @see	wcf\page\AbstractPage::$enableTracking
	 */
	public $enableTracking = true;
	
	/**
	 * true enables the usage of cookies 
	 * @var	boolean
	 */
	public $useCookies = 1;
	
	/**
	 * @see	wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'register') {
			// if the usernamefield is an email, save it as email for the registration
			if (UserUtil::isValidEmail($this->username)) {
				WCF::getSession()->register('__email', $this->username);
			} else {
				WCF::getSession()->register('__username', $this->username);
			}
			WCF::getSession()->update();
			HeaderUtil::redirect(LinkHandler::getInstance()->getLink('Register'));
			exit;
		}
		
		$this->useCookies = 0;
		if (isset($_POST['useCookies'])) $this->useCookies = intval($_POST['useCookies']);
		if (isset($_POST['url'])) $this->url = StringUtil::trim($_POST['url']);
	}
	
	/**
	 * @see	wcf\form\IForm::save()
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
		HeaderUtil::delayedRedirect($this->url, WCF::getLanguage()->get('wcf.user.login.redirect'));
		exit;
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'useCookies' => $this->useCookies,
			'supportsPersistentLogins' => UserAuthenticationFactory::getInstance()->getUserAuthentication()->supportsPersistentLogins()
		));
	}
	
	/**
	 * Gets the redirect url.
	 */
	protected function checkURL() {
		if (empty($this->url) || mb_strpos($this->url, 'index.php/Login/') !== false) {
			$this->url = LinkHandler::getInstance()->getLink();
		}
		// append missing session id
		else if (SID_ARG_1ST != '' && !preg_match('/(?:&|\?)s=[a-z0-9]{40}/', $this->url)) {
			if (mb_strpos($this->url, '?') !== false) $this->url .= SID_ARG_2ND_NOT_ENCODED;
			else $this->url .= SID_ARG_1ST;
		}
	}
}
