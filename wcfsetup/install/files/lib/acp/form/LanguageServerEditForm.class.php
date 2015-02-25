<?php
namespace wcf\acp\form;
use wcf\data\language\server\LanguageServer;
use wcf\data\language\server\LanguageServerAction;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows the language server edit form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class LanguageServerEditForm extends LanguageServerAddForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.language.server';
	
	/**
	 * language server id
	 * @var	integer
	 */
	public $languageServerID = 0;
	
	/**
	 * active language server
	 * @var	\wcf\data\language\server\LanguageServer
	 */
	public $languageServer = null;
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->languageServerID = intval($_REQUEST['id']);
		$this->languageServer = new LanguageServer($this->languageServerID);
		if (!$this->languageServer->languageServerID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		AbstractForm::save();
		
		// save server
		$this->objectAction = new LanguageServerAction(array($this->languageServerID), 'update', array('data' => array_merge($this->additionalFields, array(
			'serverURL' => $this->server
		))));
		$this->objectAction->executeAction();
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			$this->server = $this->languageServer->serverURL;
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
			
		WCF::getTPL()->assign(array(
			'languageServerID' => $this->languageServerID,
			'languageServer' => $this->languageServer,
			'action' => 'edit'
		));
	}
}
