<?php
namespace wcf\data\user\group;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\cache\CacheHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\language\LanguageFactory;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Executes user group-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.group
 * @category	Community Framework
 */
class UserGroupAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	public $className = 'wcf\data\user\group\UserGroupEditor';
	
	/**
	 * editor object for the copied user group
	 * @var	\wcf\data\user\group\UserGroupEditor
	 */
	public $groupEditor = null;
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsCreate
	 */
	protected $permissionsCreate = array('admin.user.canAddGroup');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.user.canDeleteGroup');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.user.canEditGroup');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$requireACP
	 */
	protected $requireACP = array('copy', 'create', 'delete', 'update');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::create()
	 */
	public function create() {
		$group = parent::create();
		
		if (isset($this->parameters['options'])) {
			$groupEditor = new UserGroupEditor($group);
			$groupEditor->updateGroupOptions($this->parameters['options']);
		}
		
		return $group;
	}
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::update()
	 */
	public function update() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		foreach ($this->objects as $object) {
			$object->update($this->parameters['data']);
			$object->updateGroupOptions($this->parameters['options']);
		}
	}
	
	/**
	 * Validates the 'copy' action.
	 */
	public function validateCopy() {
		WCF::getSession()->checkPermissions(array(
			'admin.user.canAddGroup',
			'admin.user.canEditGroup'
		));
		
		$this->readBoolean('copyACLOptions');
		$this->readBoolean('copyMembers');
		$this->readBoolean('copyUserGroupOptions');
		
		$this->groupEditor = $this->getSingleObject();
		if (!$this->groupEditor->isAccessible() || $this->groupEditor->groupType != UserGroup::OTHER) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Copies a user group.
	 */
	public function copy() {
		// fetch user group option values
		if ($this->parameters['copyUserGroupOptions']) {
			$sql = "SELECT	optionID, optionValue
				FROM	wcf".WCF_N."_user_group_option_value
				WHERE	groupID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($this->groupEditor->groupID));
		}
		else {
			$sql = "SELECT	optionID, defaultValue AS optionValue
				FROM	wcf".WCF_N."_user_group_option";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
		}
		
		$optionValues = array();
		while ($row = $statement->fetchArray()) {
			$optionValues[$row['optionID']] = $row['optionValue'];
		}
		
		$groupAction = new UserGroupAction(array(), 'create', array(
			'data' => array(
				'groupName' => $this->groupEditor->groupName,
				'groupDescription' => $this->groupEditor->groupDescription,
				'priority' => $this->groupEditor->priority,
				'userOnlineMarking' => $this->groupEditor->userOnlineMarking,
				'showOnTeamPage' => $this->groupEditor->showOnTeamPage
			),
			'options' => $optionValues
		));
		$returnValues = $groupAction->executeAction();
		$group = $returnValues['returnValues'];
		$groupEditor = new UserGroupEditor($group);
		
		// update group name
		$groupName = $this->groupEditor->groupName;
		if (preg_match('~^wcf\.acp\.group\.group\d+$~', $this->groupEditor->groupName)) {
			$groupName = 'wcf.acp.group.group'.$group->groupID;
			
			// create group name language item
			$sql = "INSERT INTO	wcf".WCF_N."_language_item
						(languageID, languageItem, languageItemValue, languageItemOriginIsSystem, languageCategoryID, packageID)
				SELECT		languageID, '".$groupName."', CONCAT(languageItemValue, ' (2)'), 0, languageCategoryID, packageID
				FROM		wcf".WCF_N."_language_item
				WHERE		languageItem = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($this->groupEditor->groupName));
		}
		else {
			$groupName .= ' (2)';
		}
		
		// update group name
		$groupDescription = $this->groupEditor->groupName;
		if (preg_match('~^wcf\.acp\.group\.groupDescription\d+$~', $this->groupEditor->groupDescription)) {
			$groupDescription = 'wcf.acp.group.groupDescription'.$group->groupID;
			
			// create group name language item
			$sql = "INSERT INTO	wcf".WCF_N."_language_item
						(languageID, languageItem, languageItemValue, languageItemOriginIsSystem, languageCategoryID, packageID)
				SELECT		languageID, '".$groupDescription."', languageItemValue, 0, languageCategoryID, packageID
				FROM		wcf".WCF_N."_language_item
				WHERE		languageItem = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($this->groupEditor->groupDescription));
		}
		
		$groupEditor->update(array(
			'groupDescription' => $groupDescription,
			'groupName' => $groupName
		));
		
		// copy members
		if ($this->parameters['copyMembers']) {
			$sql = "INSERT INTO	wcf".WCF_N."_user_to_group
						(userID, groupID)
				SELECT		userID, ".$group->groupID."
				FROM		wcf".WCF_N."_user_to_group
				WHERE		groupID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($this->groupEditor->groupID));
		}
		
		// copy acl options
		if ($this->parameters['copyACLOptions']) {
			$sql = "INSERT INTO	wcf".WCF_N."_acl_option_to_group
						(optionID, objectID, groupID, optionValue)
				SELECT		optionID, objectID, ".$group->groupID.", optionValue
				FROM		wcf".WCF_N."_acl_option_to_group
				WHERE		groupID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($this->groupEditor->groupID));
			
			// it is likely that applications or plugins use caches
			// for acl option values like for the labels which have
			// to be renewed after copying the acl options; because
			// there is no other way to delete these caches, we simply
			// delete all caches
			CacheHandler::getInstance()->flushAll();
		}
		
		// reset language cache
		LanguageFactory::getInstance()->deleteLanguageCache();
		
		UserGroupEditor::resetCache();
		
		return array(
			'redirectURL' => LinkHandler::getInstance()->getLink('UserGroupEdit', array(
				'id' => $group->groupID
			))
		);
	}
}
