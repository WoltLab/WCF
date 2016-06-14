<?php
namespace wcf\acp\form;
use wcf\data\template\group\TemplateGroup;
use wcf\data\template\group\TemplateGroupAction;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Shows the form for editing template groups.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class TemplateGroupEditForm extends TemplateGroupAddForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.template';
	
	/**
	 * template group id
	 * @var	integer
	 */
	public $templateGroupID = 0;
	
	/**
	 * template group object
	 * @var	\wcf\data\template\group\TemplateGroup
	 */
	public $templateGroup = null;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->templateGroupID = intval($_REQUEST['id']);
		$this->templateGroup = new TemplateGroup($this->templateGroupID);
		if (!$this->templateGroup->templateGroupID) {
			throw new IllegalLinkException();
		}
		if ($this->templateGroup->isImmutable()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function validateName() {
		if ($this->templateGroupName != $this->templateGroup->templateGroupName) {
			parent::validateName();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function validateFolderName() {
		if ($this->templateGroupFolderName != $this->templateGroup->templateGroupFolderName) {
			parent::validateFolderName();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		$this->objectAction = new TemplateGroupAction([$this->templateGroup], 'update', ['data' => array_merge($this->additionalFields, [
			'templateGroupName' => $this->templateGroupName,
			'templateGroupFolderName' => $this->templateGroupFolderName,
			'parentTemplateGroupID' => ($this->parentTemplateGroupID ?: null)
		])]);
		$this->objectAction->executeAction();
		$this->saved();
		
		// show success
		WCF::getTPL()->assign([
			'success' => true
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		$this->availableTemplateGroups = TemplateGroup::getSelectList([$this->templateGroupID, -1], 1);
		
		AbstractForm::readData();
		
		// default values
		if (!count($_POST)) {
			$this->templateGroupName = $this->templateGroup->templateGroupName;
			$this->templateGroupFolderName = $this->templateGroup->templateGroupFolderName;
			$this->parentTemplateGroupID = $this->templateGroup->parentTemplateGroupID;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'action' => 'edit',
			'templateGroupID' => $this->templateGroupID,
			'templateGroup' => $this->templateGroup
		]);
	}
}
