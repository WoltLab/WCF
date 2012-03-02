<?php
namespace wcf\system\clipboard\action;
use wcf\data\user\group\UserGroup;
use wcf\system\clipboard\ClipboardEditorItem;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Prepares clipboard editor items for user objects.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.clipboard.action
 * @category 	Community Framework
 */
class UserClipboardAction implements IClipboardAction {
	/**
	 * @see	wcf\system\clipboard\action\IClipboardAction::getTypeName()
	 */
	public function getTypeName() {
		return 'com.woltlab.wcf.user';
	}
	
	/**
	 * @see	wcf\system\clipboard\action\IClipboardAction::execute()
	 */
	public function execute(array $objects, $actionName) {
		$item = new ClipboardEditorItem();
		
		// handle actions
		switch ($actionName) {
			case 'assignToGroup':
				$item->setName('user.assignToGroup');
				$item->setURL(LinkHandler::getInstance()->getLink('UserAssignToGroup'));
			break;
			
			case 'delete':
				$count = $this->validateDelete($objects);
				if (!$count) {
					return null;
				}
				
				$item->addInternalData('confirmMessage', WCF::getLanguage()->getDynamicVariable('wcf.clipboard.item.user.delete.confirmMessage', array('count' => $count)));
				$item->addParameter('actionName', 'delete');
				$item->addParameter('className', 'wcf\data\user\UserAction');
				$item->setName('user.delete');
			break;
			
			case 'exportMailAddress':
				$item->setName('user.exportMailAddress');
				$item->setURL(LinkHandler::getInstance()->getLink('UserEmailAddressExport'));
			break;
			
			case 'sendMail':
				$item->setName('user.sendMail');
				$item->setURL(LinkHandler::getInstance()->getLink('UserMail'));
			break;
			
			default:
				throw new SystemException("action '".$actionName."' is invalid");
			break;
		}
		
		return $item;
	}
	
	/**
	 * Returns number of users which can be deleted.
	 * 
	 * @param	array<wcf\data\user\User>	$objects
	 * @return	integer
	 */
	protected function validateDelete(array $objects) {
		// check permissions
		if (!WCF::getSession()->getPermission('admin.user.canDeleteUser')) {
			return 0;
		}
		
		// user cannot delete itself
		$count = count($objects);
		$userIDs = array_keys($objects);
		foreach ($userIDs as $index => $userID) {
			if ($userID == WCF::getUser()->userID) {
				$count--;
				unset($objects[$userID]);
				unset($userIDs[$index]);
			}
		}
		
		// no valid users found
		if (!$count) return 0;
		
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
		$count = count($objects);
		foreach ($userIDs as $index => $userID) {
			if (!isset($userToGroup[$userID])) {
				$count--;
				continue;
			}
			
			if (!UserGroup::isAccessibleGroup($userToGroup[$userID])) {
				$count--;
			}
		}
		
		return $count;
	}
	
	/**
	 * @see	wcf\system\clipboard\action\IClipboardAction::getEditorLabel()
	 */
	public function getEditorLabel(array $objects) {
		return WCF::getLanguage()->getDynamicVariable('wcf.clipboard.label.user.marked', array('count' => count($objects)));
	}
}
