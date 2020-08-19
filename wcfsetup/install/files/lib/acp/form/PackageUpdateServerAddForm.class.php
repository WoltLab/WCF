<?php
namespace wcf\acp\form;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\data\package\update\server\PackageUpdateServerAction;
use wcf\data\package\update\server\PackageUpdateServerList;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\system\WCFACP;
use wcf\util\StringUtil;
use wcf\util\Url;

/**
 * Shows the server add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class PackageUpdateServerAddForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.package.server.add';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.configuration.package.canEditServer'];
	
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
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['serverURL'])) $this->serverURL = StringUtil::trim($_POST['serverURL']);
		if (isset($_POST['loginUsername'])) $this->loginUsername = $_POST['loginUsername'];
		if (isset($_POST['loginPassword'])) $this->loginPassword = $_POST['loginPassword'];
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		$this->validateServerURL();
	}
	
	/**
	 * Validates the server URL.
	 */
	protected function validateServerURL() {
		if (empty($this->serverURL)) {
			throw new UserInputException('serverURL');
		}
		
		if (!PackageUpdateServer::isValidServerURL($this->serverURL)) {
			throw new UserInputException('serverURL', 'invalid');
		}
		
		if (preg_match('/^.*\.woltlab.com$/', Url::parse($this->serverURL)['host'])) {
			throw new UserInputException('serverURL', 'woltlab');
		}
		
		if (($duplicate = $this->findDuplicateServer())) {
			throw new UserInputException('serverURL', [
				'duplicate' => $duplicate,
			]);
		}
	}
	
	/**
	 * Returns the first package update server with a matching serverURL.
	 */
	protected function findDuplicateServer() {
		$packageServerList = new PackageUpdateServerList();
		$packageServerList->readObjects();
		foreach ($packageServerList as $packageServer) {
			if ($packageServer->serverURL == $this->serverURL) {
				return $packageServer;
			}
		}
		
		return null;
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		// save server
		$this->objectAction = new PackageUpdateServerAction([], 'create', ['data' => array_merge($this->additionalFields, [
			'serverURL' => $this->serverURL,
			'loginUsername' => $this->loginUsername,
			'loginPassword' => $this->loginPassword
		])]);
		$returnValues = $this->objectAction->executeAction();
		$this->saved();
		
		// reset values
		$this->serverURL = $this->loginUsername = $this->loginPassword = '';
		
		// show success message
		WCF::getTPL()->assign([
			'success' => true,
			'objectEditLink' => LinkHandler::getInstance()->getControllerLink(PackageUpdateServerEditForm::class, ['id' => $returnValues['returnValues']->packageUpdateServerID]),
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'serverURL' => $this->serverURL,
			'loginUsername' => $this->loginUsername,
			'loginPassword' => $this->loginPassword,
			'action' => 'add'
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function show() {
		// check master password
		WCFACP::checkMasterPassword();
		
		parent::show();
	}
}
