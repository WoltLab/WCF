<?php
namespace wcf\acp\form;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\data\package\update\server\PackageUpdateServerAction;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\system\WCFACP;
use wcf\util\StringUtil;

/**
 * Shows the server add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class PackageUpdateServerAddForm extends AbstractForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.package';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.package.canEditServer');
	
	/**
	 * server url
	 * @var	string
	 */
	public $serverURL = '';
	
	/**
	 * server login username
	 * @var	string
	 */
	public $loginUsername = '';
	
	/**
	 * server login password
	 * @var	string
	 */
	public $loginPassword = '';
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['serverURL'])) $this->serverURL = StringUtil::trim($_POST['serverURL']);
		if (isset($_POST['loginUsername'])) $this->loginUsername = $_POST['loginUsername'];
		if (isset($_POST['loginPassword'])) $this->loginPassword = $_POST['loginPassword'];
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->serverURL)) {
			throw new UserInputException('serverURL');
		}
		
		if (!PackageUpdateServer::isValidServerURL($this->serverURL)) {
			throw new UserInputException('serverURL', 'notValid');
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// save server
		$this->objectAction = new PackageUpdateServerAction(array(), 'create', array('data' => array_merge($this->additionalFields, array(
			'serverURL' => $this->serverURL,
			'loginUsername' => $this->loginUsername,
			'loginPassword' => $this->loginPassword
		))));
		$this->objectAction->executeAction();
		$this->saved();
		
		// reset values
		$this->serverURL = $this->loginUsername = $this->loginPassword = '';
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'serverURL' => $this->serverURL,
			'loginUsername' => $this->loginUsername,
			'loginPassword' => $this->loginPassword,
			'action' => 'add'
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function show() {
		// check master password
		WCFACP::checkMasterPassword();
		
		parent::show();
	}
}
