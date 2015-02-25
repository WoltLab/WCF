<?php
namespace wcf\acp\form;
use wcf\data\template\group\TemplateGroup;
use wcf\data\template\group\TemplateGroupAction;
use wcf\data\template\group\TemplateGroupList;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows the form for editing template groups.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class TemplateGroupEditForm extends TemplateGroupAddForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
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
	 * @see	\wcf\patge\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->templateGroupID = intval($_REQUEST['id']);
		$this->templateGroup = new TemplateGroup($this->templateGroupID);
		if (!$this->templateGroup->templateGroupID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see	\wcf\acp\form\TemplateGroupAddForm::validateName()
	 */
	protected function validateName() {
		if ($this->templateGroupName != $this->templateGroup->templateGroupName) {
			parent::validateName();
		}
	}
	
	/**
	 * @see	\wcf\acp\form\TemplateGroupAddForm::validateFolderName()
	 */
	protected function validateFolderName() {
		if ($this->templateGroupFolderName != $this->templateGroup->templateGroupFolderName) {
			parent::validateFolderName();
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		AbstractForm::save();
		
		$this->objectAction = new TemplateGroupAction(array($this->templateGroup), 'update', array('data' => array_merge($this->additionalFields, array(
			'templateGroupName' => $this->templateGroupName,
			'templateGroupFolderName' => $this->templateGroupFolderName,
			'parentTemplateGroupID' => ($this->parentTemplateGroupID ?: null)
		))));
		$this->objectAction->executeAction();
		$this->saved();
		
		// show success
		WCF::getTPL()->assign(array(
			'success' => true
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		$this->availableTemplateGroups = TemplateGroup::getSelectList(array($this->templateGroupID), 1);
		
		AbstractForm::readData();
		
		// default values
		if (!count($_POST)) {
			$this->templateGroupName = $this->templateGroup->templateGroupName;
			$this->templateGroupFolderName = $this->templateGroup->templateGroupFolderName;
			$this->parentTemplateGroupID = $this->templateGroup->parentTemplateGroupID;
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'edit',
			'templateGroupID' => $this->templateGroupID,
			'templateGroup' => $this->templateGroup
		));
	}
}
