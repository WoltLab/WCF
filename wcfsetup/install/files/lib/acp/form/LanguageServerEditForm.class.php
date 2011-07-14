<?php
namespace wcf\acp\form;
use wcf\data\language\server\LanguageServer;
use wcf\data\language\server\LanguageServerAction;
use wcf\form\AbstractForm;
use wcf\system\WCF;
use wcf\system\exception\IllegalLinkException;

/**
 * Shows the language server edit form.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class LanguageServerEditForm extends LanguageServerAddForm {
	/**
	 * @see wcf\acp\form\ACPForm::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.language.server';
	
	/**
	 * language server id
	 * @var integer
	 */
	public $languageServerID = 0;
	
	/**
	 * active language server
	 * @var	wcf\data\language\server\LanguageServer
	 */
	public $languageServer = null;
	
	/**
	 * @see wcf\page\Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['languageServerID'])) $this->languageServerID = intval($_REQUEST['languageServerID']);
		$this->languageServer = new LanguageServer($this->languageServerID);
		if (!$this->languageServer->languageServerID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see wcf\form\Form::save()
	 */
	public function save() {
		AbstractForm::save();
		
		// save server
		$languageServerAction = new LanguageServerAction(array($this->languageServerID), 'update', array('data' => array(
			'serverURL' => $this->server
		)));
		$languageServerAction->executeAction();
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
			$this->server = $this->languageServer->serverURL;
		}
	}
	
	/**
	 * @see Page::assignVariables()
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
?>
