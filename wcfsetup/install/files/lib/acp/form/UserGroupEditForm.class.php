<?php
namespace wcf\acp\form;
use wcf\data\user\group\UserGroup;
use wcf\data\user\group\UserGroupAction;
use wcf\data\user\group\UserGroupEditor;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * Shows the group edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class UserGroupEditForm extends UserGroupAddForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.group';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.user.canEditGroup'];
	
	/**
	 * id of the edited user group
	 * @var	integer
	 */
	public $groupID = 0;
	
	/**
	 * user group editor object
	 * @var	UserGroupEditor
	 */
	public $group = null;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get group
		if (isset($_REQUEST['id'])) $this->groupID = intval($_REQUEST['id']);
		$group = new UserGroup($this->groupID);
		if (!$group->groupID) {
			throw new IllegalLinkException();
		}
		if (!$group->isAccessible()) {
			throw new PermissionDeniedException();
		}
		
		$this->group = new UserGroupEditor($group);
		
		/** @noinspection PhpUndefinedMethodInspection */
		$this->optionHandler->setUserGroup($group);
		/** @noinspection PhpUndefinedMethodInspection */
		$this->optionHandler->init();
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initOptionHandler() {
		// does nothing, we call OptionHandler::init() after we set the
		// user group
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		if (empty($_POST)) {
			I18nHandler::getInstance()->setOptions('groupName', 1, $this->group->groupName, 'wcf.acp.group.group\d+');
			I18nHandler::getInstance()->setOptions('groupDescription', 1, $this->group->groupDescription, 'wcf.acp.group.groupDescription\d+');
			$this->groupName = $this->group->groupName;
			$this->groupDescription = $this->group->groupDescription;
			$this->priority = $this->group->priority;
			$this->userOnlineMarking = $this->group->userOnlineMarking;
			$this->showOnTeamPage = $this->group->showOnTeamPage;
		}
		
		parent::readData();
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables(!empty($_POST));
		
		WCF::getTPL()->assign([
			'groupID' => $this->group->groupID,
			'group' => $this->group,
			'action' => 'edit',
			'availableUserGroups' => UserGroup::getAccessibleGroups()
		]);
		
		// add warning when the initiator is in the group
		if ($this->group->isMember()) {
			WCF::getTPL()->assign('warningSelfEdit', true);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		// save group
		$optionValues = $this->optionHandler->save();
		$this->groupName = 'wcf.acp.group.group'.$this->group->groupID;
		if (I18nHandler::getInstance()->isPlainValue('groupName')) {
			I18nHandler::getInstance()->remove($this->groupName);
			$this->groupName = I18nHandler::getInstance()->getValue('groupName');
			
			UserGroup::getGroupByID($this->groupID)->setName($this->groupName);
		}
		else {
			I18nHandler::getInstance()->save('groupName', $this->groupName, 'wcf.acp.group', 1);
			
			$groupNames = I18nHandler::getInstance()->getValues('groupName');
			UserGroup::getGroupByID($this->groupID)->setName($groupNames[WCF::getLanguage()->languageID]);
		}
		$this->groupDescription = 'wcf.acp.group.groupDescription'.$this->group->groupID;
		if (I18nHandler::getInstance()->isPlainValue('groupDescription')) {
			I18nHandler::getInstance()->remove($this->groupDescription);
			$this->groupDescription = I18nHandler::getInstance()->getValue('groupDescription');
		}
		else {
			I18nHandler::getInstance()->save('groupDescription', $this->groupDescription, 'wcf.acp.group', 1);
		}
		
		$this->objectAction = new UserGroupAction([$this->groupID], 'update', [
			'data' => array_merge($this->additionalFields, [
				'groupName' => $this->groupName,
				'groupDescription' => $this->groupDescription,
				'priority' => $this->priority,
				'userOnlineMarking' => $this->userOnlineMarking,
				'showOnTeamPage' => $this->showOnTeamPage
			]),
			'options' => $optionValues
		]);
		$this->objectAction->executeAction();
		$this->saved();
		
		// reset user group cache
		UserGroupEditor::resetCache();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
}
