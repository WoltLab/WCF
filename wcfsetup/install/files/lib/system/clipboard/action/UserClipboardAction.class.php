<?php
namespace wcf\system\clipboard\action;
use wcf\data\user\group\UserGroup;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Prepares clipboard editor items for user objects.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.clipboard.action
 * @category	Community Framework
 */
class UserClipboardAction extends AbstractClipboardAction {
	/**
	 * @see	wcf\system\clipboard\action\AbstractClipboardAction::$actionClassActions
	 */
	protected $actionClassActions = array('delete');
	
	/**
	 * @see	wcf\system\clipboard\action\AbstractClipboardAction::$supportedActions
	 */
	protected $supportedActions = array('assignToGroup', 'delete', 'exportMailAddress', 'sendMail');
	
	/**
	 * @see	wcf\system\clipboard\action\IClipboardAction::execute()
	 */
	public function execute(array $objects, $actionName) {
		$item = parent::execute($objects, $actionName);
		
		if ($item === null) {
			return null;
		}
		
		// handle actions
		switch ($actionName) {
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
		}
		
		return $item;
	}
	
	/**
	 * @see	wcf\system\clipboard\action\IClipboardAction::getClassName()
	 */
	public function getClassName() {
		return 'wcf\data\user\UserAction';
	}
	
	/**
	 * @see	wcf\system\clipboard\action\IClipboardAction::getTypeName()
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
			return 0;
		}
		
		// user cannot delete itself
		$userIDs = array_keys($this->objects);
		foreach ($userIDs as $index => $userID) {
			if ($userID == WCF::getUser()->userID) {
				unset($userIDs[$index]);
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
}
