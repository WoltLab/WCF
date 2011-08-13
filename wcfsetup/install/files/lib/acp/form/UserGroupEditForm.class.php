<?php
namespace wcf\acp\form;
use wcf\data\acp\session\ACPSession;
use wcf\data\user\group\UserGroup;
use wcf\data\user\group\UserGroupAction;
use wcf\data\user\group\UserGroupEditor;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Shows the group edit form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class UserGroupEditForm extends UserGroupAddForm {
	/**
	 * @see wcf\acp\form\UserGroupAddForm::$menuItemName
	 */
	public $menuItemName = 'wcf.acp.menu.link.group';
	
	/**
	 * @see wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.user.canEditGroup');
	
	/**
	 * group id
	 * @var integer
	 */
	public $groupID = 0;
	
	/**
	 * group editor object
	 * @var GroupEditor
	 */
	public $group = null;
	
	/**
	 * @see wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get group
		if (isset($_REQUEST['groupID'])) $this->groupID = intval($_REQUEST['groupID']);
		$group = new UserGroup($this->groupID);
		if (!$group->groupID) {
			throw new IllegalLinkException();
		}
		if (!$group->isAccessible()) {
			throw new PermissionDeniedException();
		}
		
		$this->group = new UserGroupEditor($group);
	}
	
	/**
	 * @see wcf\page\IPage::readData()
	 */
	public function readData() {
		if (!count($_POST)) {			
			// get default values
			if ($this->group->groupType != UserGroup::EVERYONE) {
				$defaultGroup = UserGroup::getGroupByType(UserGroup::EVERYONE);
				foreach ($this->options as $option) {
					$value = $defaultGroup->getGroupOption($option->optionName);
					if ($value !== null) {
						$this->optionValues[$option->optionName] = $value;
					}
				}
			}
			
			foreach ($this->options as $option) {
				$value = $this->group->getGroupOption($option->optionName);
				if ($value !== null) {
					$this->optionValues[$option->optionName] = $value;
				}
			}
			
			// get group name in the available languages
			foreach ($this->languageCodes as $languageID => $languageCode) {
				$this->groupName[$languageID] = LanguageFactory::getLanguage($languageID)->get($this->group->groupIdentifier);
			}
		}
		
		// set group identifier
		$this->groupIdentifier = $this->group->groupIdentifier;
		
		parent::readData();
	}
	
	/**
	 * @see wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'groupID' => $this->group->groupID,
			'action' => 'edit'
		));
		
		// add warning when the initiator is in the group
		if ($this->group->isMember($this->groupIdentifier)) {
			WCF::getTPL()->assign('warningSelfEdit', true);
		}
	}
	
	/**
	 * @see wcf\form\IForm::save()
	 */
	public function save() {
		AbstractForm::save();
		
		// save group
		$saveOptions = array();
		if ($this->group->groupType == UserGroup::EVERYONE) {
			foreach ($this->options as $option) {
				$saveOptions[$option->optionID] = $this->optionValues[$option->optionName];
			}
		}
		else {
			// get default group
			$defaultGroup = UserGroup::getGroupByType(UserGroup::EVERYONE);
			foreach ($this->options as $option) {
				if ($this->optionValues[$option->optionName] != $defaultGroup->getGroupOption($option->optionName)) {
					$saveOptions[$option->optionID] = $this->optionValues[$option->optionName];
				}
			}
		}
		$data = array(
			'data' => array_merge($this->additionalFields, array('groupName' => $this->groupName)),
			'options' => $saveOptions
		);
		$groupAction = new UserGroupAction(array($this->groupID), 'update', $data);
		$groupAction->executeAction();
		$this->saved();
		
		UserGroupEditor::resetCache();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see	wcf\acp\form\UserGroupEditForm::validateGroupIdentifier()
	 */
	protected function validateGroupIdentifier() {
		// does nothing
	}
}
