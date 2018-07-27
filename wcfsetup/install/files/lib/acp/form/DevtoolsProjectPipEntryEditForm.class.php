<?php
namespace wcf\acp\form;
use wcf\form\AbstractForm;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\exception\IllegalLinkException;
use wcf\system\form\builder\IFormDocument;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows the form to edit an exiting entry for a specific pip and project.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 * @since	3.2
 */
class DevtoolsProjectPipEntryEditForm extends DevtoolsProjectPipEntryAddForm {
	/**
	 * identifier of the edited pip entry
	 * @var	string
	 */
	public $identifier = '';
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['identifier'])) $this->identifier = StringUtil::trim($_REQUEST['identifier']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		if (!empty($_POST)) {
			$this->pipObject->getPip()->setEditedEntryIdentifier($this->identifier);
		}
		
		parent::readData();
		
		if (empty($_POST)) {
			if (!$this->pipObject->getPip()->setEntryData($this->identifier, $this->form)) {
				throw new IllegalLinkException();
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function addPipFormFields() {
		$this->form->formMode(IFormDocument::FORM_MODE_UPDATE);
		
		parent::addPipFormFields();
	}
	
	/**
	 * @inheritDoc
	 */
	public function setFormAction() {
		$this->form->action(LinkHandler::getInstance()->getLink('DevtoolsProjectPipEntryEdit', [
			'entryType' => $this->entryType,
			'id' => $this->project->projectID,
			'pip' => $this->pip,
			'identifier' => $this->identifier
		]));
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		$newIdentifier = $this->pipObject->getPip()->editEntry($this->form, $this->identifier);
		
		$this->saved();
		
		if ($this->identifier !== $newIdentifier) {
			// reload the page with the new identifier and store success
			// message in session variables
			WCF::getSession()->register($this->project->projectID . '-' . $this->pip . '-success', 1);
			
			HeaderUtil::redirect(LinkHandler::getInstance()->getLink('DevtoolsProjectPipEntryEdit', [
				'entryType' => $this->entryType,
				'id' => $this->project->projectID,
				'pip' => $this->pip,
				'identifier' => $newIdentifier
			]));
			exit;
		}
		else {
			WCF::getTPL()->assign('success', true);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		// check if a success message has been stored in session variables
		// from previous request 
		if (WCF::getSession()->getVar($this->project->projectID . '-' . $this->pip . '-success') == 1) {
			WCF::getSession()->unregister($this->project->projectID . '-' . $this->pip . '-success');
			
			WCF::getTPL()->assign('success', true);
		}
		
		WCF::getTPL()->assign([
			'action' => 'edit'
		]);
	}
}
