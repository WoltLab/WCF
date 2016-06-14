<?php
namespace wcf\acp\form;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\data\package\update\server\PackageUpdateServerAction;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows the server edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class PackageUpdateServerEditForm extends PackageUpdateServerAddForm {
	/**
	 * update server id
	 * @var	integer
	 */
	public $packageUpdateServerID = 0;
	
	/**
	 * active package update server
	 * @var	\wcf\data\package\update\server\PackageUpdateServer
	 */
	public $updateServer = null;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->packageUpdateServerID = intval($_REQUEST['id']);
		$this->updateServer = new PackageUpdateServer($this->packageUpdateServerID);
		if (!$this->updateServer->packageUpdateServerID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		// save server
		$this->objectAction = new PackageUpdateServerAction([$this->packageUpdateServerID], 'update', ['data' => array_merge($this->additionalFields, [
			'serverURL' => $this->serverURL,
			'loginUsername' => $this->loginUsername,
			'loginPassword' => $this->loginPassword
		])]);
		$this->objectAction->executeAction();
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			$this->serverURL = $this->updateServer->serverURL;
			$this->loginUsername = $this->updateServer->loginUsername;
			$this->loginPassword = $this->updateServer->loginPassword;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
			
		WCF::getTPL()->assign([
			'packageUpdateServerID' => $this->packageUpdateServerID,
			'packageUpdateServer' => $this->updateServer,
			'action' => 'edit'
		]);
	}
}
