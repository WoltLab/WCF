<?php
namespace wcf\acp\form;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\PasswordUtil;

/**
 * Shows the master password form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
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
	 * @see	\wcf\page\IPage::readParameters()
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
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['masterPassword'])) $this->masterPassword = $_POST['masterPassword'];
		if (isset($_POST['url'])) $this->url = $_POST['url'];
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->masterPassword)) {
			throw new UserInputException('masterPassword');
		}
		
		// check password
		if (!PasswordUtil::secureCompare(MASTER_PASSWORD, PasswordUtil::getDoubleSaltedHash($this->masterPassword, MASTER_PASSWORD))) {
			throw new UserInputException('masterPassword', 'notValid');
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
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
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST) && mb_strpos(WCF::getSession()->requestURI, 'MasterPassword') === false) {
			$this->url = WCF::getSession()->requestURI;
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'masterPassword' => $this->masterPassword,
			'relativeWcfDir' => RELATIVE_WCF_DIR,
			'url' => $this->url
		));
	}
}
