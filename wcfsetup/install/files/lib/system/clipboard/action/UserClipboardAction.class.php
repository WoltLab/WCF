<?php
namespace wcf\system\clipboard\action;
use wcf\data\clipboard\action\ClipboardAction;
use wcf\data\user\group\UserGroup;
use wcf\data\user\UserAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Prepares clipboard editor items for user objects.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Clipboard\Action
 */
class UserClipboardAction extends AbstractClipboardAction {
	/**
	 * @inheritDoc
	 */
	protected $actionClassActions = ['delete'];
	
	/**
	 * @inheritDoc
	 */
	protected $supportedActions = ['assignToGroup', 'ban', 'delete', 'enable', 'exportMailAddress', 'merge', 'sendMail', 'sendNewPassword'];
	
	/**
	 * @inheritDoc
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
				$item->addInternalData('confirmMessage', WCF::getLanguage()->getDynamicVariable('wcf.clipboard.item.com.woltlab.wcf.user.delete.confirmMessage', [
					'count' => $item->getCount()
				]));
			break;
			
			case 'exportMailAddress':
				$item->setURL(LinkHandler::getInstance()->getLink('UserEmailAddressExport'));
			break;
			
			case 'merge':
				$item->setURL(LinkHandler::getInstance()->getLink('UserMerge'));
			break;
			
			case 'sendMail':
				$item->setURL(LinkHandler::getInstance()->getLink('UserMail'));
			break;
			
			case 'sendNewPassword':
				$item->addParameter('confirmMessage', WCF::getLanguage()->getDynamicVariable('wcf.clipboard.item.com.woltlab.wcf.user.sendNewPassword.confirmMessage', [
					'count' => $item->getCount()
				]));
			break;
		}
		
		return $item;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getClassName() {
		return UserAction::class;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTypeName() {
		return 'com.woltlab.wcf.user';
	}
	
	/**
	 * Returns the ids of the users which can be deleted.
	 * 
	 * @return	integer[]
	 */
	protected function validateDelete() {
		// check permissions
		if (!WCF::getSession()->getPermission('admin.user.canDeleteUser')) {
			return [];
		}
		
		return $this->__validateAccessibleGroups(array_keys($this->objects));
	}
	
	/**
	 * Returns the ids of the users which can be banned.
	 * 
	 * @return	integer[]
	 */
	protected function validateBan() {
		// check permissions
		if (!WCF::getSession()->getPermission('admin.user.canBanUser')) {
			return [];
		}
		
		return $this->__validateAccessibleGroups(array_keys($this->objects));
	}
	
	/**
	 * Validates accessible groups.
	 * 
	 * @param	integer[]	$userIDs
	 * @param	boolean		$ignoreOwnUser
	 * @return	integer[]
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
		if (empty($userIDs)) return [];
		
		// fetch user to group associations
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID IN (?)", [$userIDs]);
		
		$sql = "SELECT	userID, groupID
			FROM	wcf".WCF_N."_user_to_group
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		$userToGroup = [];
		while ($row = $statement->fetchArray()) {
			if (!isset($userToGroup[$row['userID']])) {
				$userToGroup[$row['userID']] = [];
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
	 * @return	integer[]
	 */
	public function validateSendNewPassword() {
		// check permissions
		if (!WCF::getSession()->getPermission('admin.user.canEditPassword')) {
			return [];
		}
		
		return $this->__validateAccessibleGroups(array_keys($this->objects));
	}
	
	/**
	 * Returns the ids of the users which can be enabled.
	 * 
	 * @return	integer[]
	 * @since	3.0
	 */
	protected function validateEnable() {
		// check permissions
		if (!WCF::getSession()->getPermission('admin.user.canEnableUser')) {
			return [];
		}
		
		$userIDs = [];
		foreach ($this->objects as $user) {
			if ($user->activationCode) $userIDs[] = $user->userID;
		}
		
		return $userIDs;
	}
	
	/**
	 * Returns the ids of the users which can be merge.
	 * 
	 * @return	integer[]
	 * @since	3.0
	 */
	protected function validateMerge() {
		// check permissions
		if (!WCF::getSession()->getPermission('admin.user.canEditUser')) {
			return [];
		}
		
		$userIDs = array_keys($this->objects);
		if (count($userIDs) < 2) return [];
		
		return $userIDs;
	}
}
