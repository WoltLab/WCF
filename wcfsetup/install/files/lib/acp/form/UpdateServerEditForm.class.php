<?php
namespace wcf\acp\form;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\data\package\update\server\PackageUpdateServerAction;
use wcf\form\AbstractForm;
use wcf\system\WCF;
use wcf\system\exception\IllegalLinkException;

/**
 * Shows the server edit form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class UpdateServerEditForm extends UpdateServerAddForm {
	/**
	 * @see ACPForm::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.package.server';
	
	/**
	 * update server id
	 * @var integer
	 */
	public $packageUpdateServerID = 0;
	
	/**
	 * active package update server
	 * @var	PackageUpdateServer
	 */
	public $updateServer = null;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['packageUpdateServerID'])) $this->packageUpdateServerID = intval($_REQUEST['packageUpdateServerID']);
		$this->updateServer = new PackageUpdateServer($this->packageUpdateServerID);
		if (!$this->updateServer->packageUpdateServerID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		AbstractForm::save();
		
		// save server
		$updateServerAction = new PackageUpdateServerAction(array($this->packageUpdateServerID), 'update', array('data' => array(
			'server' => $this->server,
			'loginUsername' => $this->loginUsername,
			'loginPassword' => $this->loginPassword
		)));
		$updateServerAction->executeAction();
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			$this->server = $this->updateServer->server;
			$this->loginUsername = $this->updateServer->loginUsername;
			$this->loginPassword = $this->updateServer->loginPassword;
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
			
		WCF::getTPL()->assign(array(
			'packageUpdateServerID' => $this->packageUpdateServerID,
			'packageUpdateServer' => $this->updateServer,
			'action' => 'edit'
		));
	}
}
?>