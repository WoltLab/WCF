<?php
namespace wcf\system\clipboard\action;
use wcf\data\clipboard\action\ClipboardAction;
use wcf\data\user\group\UserGroup;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Prepares clipboard editor items for user objects.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.clipboard.action
 * @category	Community Framework
 */
class UserClipboardAction extends AbstractClipboardAction {
	/**
	 * @see	\wcf\system\clipboard\action\AbstractClipboardAction::$actionClassActions
	 */
	protected $actionClassActions = array('delete');
	
	/**
	 * @see	\wcf\system\clipboard\action\AbstractClipboardAction::$supportedActions
	 */
	protected $supportedActions = array('assignToGroup', 'ban', 'delete', 'exportMailAddress', 'sendMail', 'sendNewPassword');
	
	/**
	 * @see	\wcf\system\clipboard\action\IClipboardAction::execute()
	 */
	public function execute(array $objects, ClipboardAction $action) {
		$item = parent::execute($objects, $action);
		
		if ($item === null) {
			return null;
		}
		
		// handle actions
		switch ($action->actionName) {
			case 'assignToGroup':
				$item->setURL(LinkHandler::getInstance()->getLink('UserAssignToGroup'));
			break;
			
			case 'delete':
				$item->addInternalData('confirmMessage', WCF::getLanguage()->getDynamicVariable('wcf.clipboard.item.com.woltlab.wcf.user.delete.confirmMessage', array(
					'count' => $item->getCount()
				)));
			break;
			
			case 'exportMailAddress':
				$item->setURL(LinkHandler::getInstance()->getLink('UserEmailAddressExport'));
			break;
			
			case 'sendMail':
				$item->setURL(LinkHandler::getInstance()->getLink('UserMail'));
			break;
			
			case 'sendNewPassword':
				$item->addParameter('confirmMessage', WCF::getLanguage()->getDynamicVariable('wcf.clipboard.item.com.woltlab.wcf.user.sendNewPassword.confirmMessage', array(
					'count' => $item->getCount()
				)));
			break;
		}
		
		return $item;
	}
	
	/**
	 * @see	\wcf\system\clipboard\action\IClipboardAction::getClassName()
	 */
	public function getClassName() {
		return 'wcf\data\user\UserAction';
	}
	
	/**
	 * @see	\wcf\system\clipboard\action\IClipboardAction::getTypeName()
	 */
	public function getTypeName() {
		return 'com.woltlab.wcf.user';
	}
	
	/**
	 * Returns the ids of the users which can be deleted.
	 * 
	 * @return	array<integer>
	 */
	protected function validateDelete() {
		// check permissions
		if (!WCF::getSession()->getPermission('admin.user.canDeleteUser')) {
			return array();
		}
		
		return $this->__validateAccessibleGroups(array_keys($this->objects));
	}
	
	/**
	 * Returns the ids of the users which can be banned.
	 * 
	 * @return	array<integer>
	 */
	protected function validateBan() {
		// check permissions
		if (!WCF::getSession()->getPermission('admin.user.canBanUser')) {
			return array();
		}
		
		return $this->__validateAccessibleGroups(array_keys($this->objects));
	}
	
	/**
	 * Validates accessible groups.
	 * 
	 * @return	array<integer>
	 */
	protected function __validateAccessibleGroups(array $userIDs, $ignoreOwnUser = true) {
		if ($ignoreOwnUser) {
			foreach ($userIDs as $index => $userID) {
				if ($userID == WCF::getUser()->userID) {
					unset($userIDs[$index]);
				}
			}
		}
		
		// no valid users found
		if (empty($userIDs)) return array();
		
		// fetch user to group associations
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID IN (?)", array($userIDs));
		
		$sql = "SELECT	userID, groupID
			FROM	wcf".WCF_N."_user_to_group
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		$userToGroup = array();
		while ($row = $statement->fetchArray()) {
			if (!isset($userToGroup[$row['userID']])) {
				$userToGroup[$row['userID']] = array();
			}
			
			$userToGroup[$row['userID']][] = $row['groupID'];
		}
		
		// validate if user's group is accessible for current user
		foreach ($userIDs as $userID) {
			if (!isset($userToGroup[$userID]) || !UserGroup::isAccessibleGroup($userToGroup[$userID])) {
				unset($userIDs[$userID]);
			}
		}
		
		return $userIDs;
	}
	
	/**
	 * Returns the ids of the users which can be sent new passwords.
	 * 
	 * @return	array<integer>
	 */
	public function validateSendNewPassword() {
		// check permissions
		if (!WCF::getSession()->getPermission('admin.user.canEditPassword')) {
			return array();
		}
		
		return $this->__validateAccessibleGroups(array_keys($this->objects));
	}
}
