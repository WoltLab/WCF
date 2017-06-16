<?php
namespace wcf\acp\form;
use wcf\data\contact\recipient\ContactOption;
use wcf\data\contact\recipient\ContactOptionAction;
use wcf\data\contact\recipient\ContactOptionEditor;

/**
 * Shows the contact option add form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 * @since       3.1
 */
class ContactOptionAddForm extends AbstractCustomOptionForm {
	/**
	 * @inheritDoc
	 */
	public $action = 'add';
	
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
	 * action class name
	 * @var string
	 */
	public $actionClass = ContactOptionAction::class;
	
	/**
	 * base class name
	 * @var string
	 */
	public $baseClass = ContactOption::class;
	
	/**
	 * editor class name
	 * @var string
	 */
	public $editorClass = ContactOptionEditor::class;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->getI18nValue('optionTitle')->setLanguageItem('wcf.contact.field', 'wcf.contact', 'com.woltlab.wcf');
		$this->getI18nValue('optionDescription')->setLanguageItem('wcf.contact.fieldDescription', 'wcf.contact', 'com.woltlab.wcf');
	}
}
