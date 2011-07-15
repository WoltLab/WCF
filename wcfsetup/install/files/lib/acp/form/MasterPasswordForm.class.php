<?php
namespace wcf\acp\form;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows the master password form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class MasterPasswordForm extends ACPForm {
	/**
	 * @see wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'masterPassword';
	
	/**
	 * master password
	 *
	 * @var string
	 */
	public $masterPassword = '';
	
	/**
	 * forward url
	 *
	 * @var string
	 */
	public $url = '';
	
	/**
	 * @see wcf\page\Page::readParameters()
	 */	
	public function readParameters() {
		parent::readParameters();
		
		if (file_exists(WCF_DIR.'acp/masterPassword.inc.php')) {
			require_once(WCF_DIR.'acp/masterPassword.inc.php');
		}
	}

	/**
	 * @see wcf\form\Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['masterPassword'])) $this->masterPassword = $_POST['masterPassword'];
		if (isset($_POST['url'])) $this->url = $_POST['url'];
	}
	
	/**
	 * @see wcf\form\Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->masterPassword)) {
			throw new UserInputException('masterPassword');
		}
		
		// check password
		if (StringUtil::getSaltedHash($this->masterPassword, MASTER_PASSWORD_SALT) != MASTER_PASSWORD) {
			throw new UserInputException('masterPassword', 'invalid');
		}
	}
	
	/**
	 * @see wcf\form\Form::save()
	 */
	public function save() {
		parent::save();
		
		// update session
		WCF::getSession()->register('masterPassword', 1);
		WCF::getSession()->update();
		WCF::getSession()->disableUpdate();
		
		// forward
		if (empty($this->url)) {
			$this->url = 'index.php?page=Index'.SID_ARG_2ND_NOT_ENCODED;
		}
		HeaderUtil::redirect($this->url, false);
		exit;
	}
	
	/**
	 * @see wcf\page\Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			$this->url = WCF::getSession()->requestURI;
		}
	}
	
	/**
	 * @see wcf\page\Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'masterPassword' => $this->masterPassword,
			'url' => $this->url
		));
	}
}
