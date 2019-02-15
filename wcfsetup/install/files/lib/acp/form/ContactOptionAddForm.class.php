<?php
namespace wcf\acp\form;
use wcf\data\contact\option\ContactOption;
use wcf\data\contact\option\ContactOptionAction;
use wcf\data\contact\option\ContactOptionEditor;

/**
 * Shows the contact option add form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
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
		
		$this->getI18nValue('optionTitle')->setLanguageItem('wcf.contact.option', 'wcf.contact', 'com.woltlab.wcf');
		$this->getI18nValue('optionDescription')->setLanguageItem('wcf.contact.optionDescription', 'wcf.contact', 'com.woltlab.wcf');
	}
}
