<?php
namespace wcf\acp\form;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\data\package\update\server\PackageUpdateServerAction;
use wcf\system\WCF;
use wcf\system\WCFACP;
use wcf\system\exception\UserInputException;
use wcf\util\StringUtil;

/**
 * Shows the server add form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class UpdateServerAddForm extends ACPForm {
	/**
	 * @see AbstractPage::$templateName
	 */
	public $templateName = 'updateServerAdd';
	
	/**
	 * @see ACPForm::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.package.server.add';
	
	/**
	 * @see AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.package.canEditServer');
	
	/**
	 * server url
	 * @var string
	 */
	public $server = '';
	
	/**
	 * server login username
	 * @var string
	 */
	public $loginUsername = '';
	
	/**
	 * server login password
	 * @var string
	 */
	public $loginPassword = '';
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['server'])) $this->server = StringUtil::trim($_POST['server']);
		if (isset($_POST['loginUsername'])) $this->loginUsername = $_POST['loginUsername'];
		if (isset($_POST['loginPassword'])) $this->loginPassword = $_POST['loginPassword'];
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->server)) {
			throw new UserInputException('server');
		}
		
		if (!PackageUpdateServer::isValidServerURL($this->server)) {
			throw new UserInputException('server', 'notValid');
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// save server
		$updateServerAction = new PackageUpdateServerAction(array(), 'create', array('data' => array(
			'server' => $this->server,
			'loginUsername' => $this->loginUsername,
			'loginPassword' => $this->loginPassword
		)));
		$updateServer = $updateServerAction->executeAction();
		$this->saved();
		
		// reset values
		$this->server = $this->loginUsername = $this->loginPassword = '';
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'server' => $this->server,
			'loginUsername' => $this->loginUsername,
			'loginPassword' => $this->loginPassword,
			'action' => 'add'
		));
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function show() {
		// check master password
		WCFACP::checkMasterPassword();
		
		parent::show();
	}
}
