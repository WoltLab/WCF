<?php
namespace wcf\system\clipboard\action;
use wcf\data\clipboard\action\ClipboardAction;
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
class UserExtendedClipboardAction extends AbstractClipboardAction {
	/**
	 * @see	\wcf\system\clipboard\action\AbstractClipboardAction::$supportedActions
	 */
	protected $supportedActions = array('merge', 'enable');
	
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
			case 'merge':
				$item->setURL(LinkHandler::getInstance()->getLink('UserMerge'));
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
	 * Returns the ids of the users which can be enabled.
	 * 
	 * @return	array<integer>
	 */
	protected function validateEnable() {
		// check permissions
		if (!WCF::getSession()->getPermission('admin.user.canEnableUser')) {
			return array();
		}
		
		$userIDs = array();
		foreach ($this->objects as $user) {
			if ($user->activationCode) $userIDs[] = $user->userID;
		}
		
		return $userIDs;
	}
	
	/**
	 * Returns the ids of the users which can be merge.
	 * 
	 * @return	array<integer>
	 */
	protected function validateMerge() {
		// check permissions
		if (!WCF::getSession()->getPermission('admin.user.canEditUser')) {
			return array();
		}
		
		$userIDs = array_keys($this->objects);
		if (count($userIDs) < 2) return array();
		
		return $userIDs;
	}
}
