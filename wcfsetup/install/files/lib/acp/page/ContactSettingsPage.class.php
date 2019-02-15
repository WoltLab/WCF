<?php
namespace wcf\acp\page;
use wcf\data\contact\option\ContactOptionList;
use wcf\data\contact\recipient\ContactRecipientList;
use wcf\page\AbstractPage;
use wcf\system\WCF;

/**
 * Shows the contact form configuration page.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * @since	3.1
 */
class ContactSettingsPage extends AbstractPage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.contact.settings';
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_CONTACT_FORM'];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.contact.canManageContactForm'];
	
	/**
	 * @var ContactOptionList
	 */
	public $optionList;
	
	/**
	 * @var ContactRecipientList
	 */
	public $recipientList;
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		$this->optionList = new ContactOptionList();
		$this->optionList->readObjects();
		
		$this->recipientList = new ContactRecipientList();
		$this->recipientList->readObjects();
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'optionList' => $this->optionList,
			'recipientList' => $this->recipientList
		]);
	}
}
