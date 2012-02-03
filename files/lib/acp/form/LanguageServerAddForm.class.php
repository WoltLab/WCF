<?php
namespace wcf\acp\form;
use wcf\data\language\server\LanguageServerAction;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\system\WCF;
use wcf\system\WCFACP;
use wcf\system\exception\UserInputException;
use wcf\util\StringUtil;

/**
 * Shows the language server add form.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class LanguageServerAddForm extends ACPForm {
	/**
	 * @see wcf\acp\form\ACPForm::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.language.server.add';
	
	/**
	 * @see wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.language.canAddServer');
	
	/**
	 * server url
	 * @var string
	 */
	public $server = '';
	
	/**
	 * @see wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['server'])) $this->server = StringUtil::trim($_POST['server']);
	}
	
	/**
	 * @see wcf\form\IForm::validate()
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
	 * @see wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// save server
		$languageServerAction = new LanguageServerAction(array(), 'create', array('data' => array(
			'serverURL' => $this->server
		)));
		$languageServerAction->executeAction();
		$this->saved();
		
		// reset values
		$this->server = '';
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'server' => $this->server,
			'action' => 'add'
		));
	}
	
	/**
	 * @see wcf\page\IPage::assignVariables()
	 */
	public function show() {
		// check master password
		WCFACP::checkMasterPassword();
		
		parent::show();
	}
}
