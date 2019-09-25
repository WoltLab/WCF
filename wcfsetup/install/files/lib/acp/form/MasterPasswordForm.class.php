<?php
namespace wcf\acp\form;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\request\LinkHandler;
use wcf\system\request\RouteHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\PasswordUtil;

/**
 * Shows the master password form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class MasterPasswordForm extends AbstractForm {
	/**
	 * master password
	 * @var	string
	 */
	public $masterPassword = '';
	
	/**
	 * forward url
	 * @var	string
	 */
	public $url = '';
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (file_exists(WCF_DIR.'acp/masterPassword.inc.php')) {
			require_once(WCF_DIR.'acp/masterPassword.inc.php');
		}
		else {
			HeaderUtil::redirect(LinkHandler::getInstance()->getLink('MasterPasswordInit'));
			exit;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['masterPassword'])) $this->masterPassword = $_POST['masterPassword'];
		if (isset($_POST['url'])) $this->url = $_POST['url'];
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->masterPassword)) {
			throw new UserInputException('masterPassword');
		}
		
		// check password
		if (!\hash_equals(MASTER_PASSWORD, PasswordUtil::getDoubleSaltedHash($this->masterPassword, MASTER_PASSWORD))) {
			throw new UserInputException('masterPassword', 'invalid');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		// update session
		WCF::getSession()->register('masterPassword', 1);
		WCF::getSession()->update();
		WCF::getSession()->disableUpdate();
		
		// forward
		if (empty($this->url)) {
			$this->url = LinkHandler::getInstance()->getLink();
		}
		HeaderUtil::redirect($this->url);
		exit;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST) && mb_strpos(WCF::getSession()->requestURI, 'MasterPassword') === false) {
			// The request URI on it's own is not sufficient for environments that use different subdomains.
			$protocol = RouteHandler::secureConnection() ? 'https' : 'http';
			$this->url = $protocol . '://' . $_SERVER['HTTP_HOST'] . WCF::getSession()->requestURI;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'masterPassword' => $this->masterPassword,
			'relativeWcfDir' => RELATIVE_WCF_DIR,
			'url' => $this->url
		]);
	}
}
