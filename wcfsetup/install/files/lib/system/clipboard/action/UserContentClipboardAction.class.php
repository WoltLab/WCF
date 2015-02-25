<?php
namespace wcf\system\clipboard\action;
use wcf\data\clipboard\action\ClipboardAction;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Prepares clipboard editor items for edit history entries.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.clipboard.action
 * @category	Community Framework
 */
class UserContentClipboardAction extends AbstractClipboardAction {
	/**
	 * @see	\wcf\system\clipboard\action\AbstractClipboardAction::$supportedActions
	 */
	protected $supportedActions = array('revertContentChanges');
	
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
			case 'revertContentChanges':
				$item->setURL(LinkHandler::getInstance()->getLink('UserContentRevertChanges'));
			break;
		}
		
		return $item;
	}
	
	/**
	 * @see	\wcf\system\clipboard\action\IClipboardAction::getClassName()
	 */
	public function getClassName() {
		return 'wcf\data\user\UserContentAction';
	}
	
	/**
	 * @see	\wcf\system\clipboard\action\IClipboardAction::getTypeName()
	 */
	public function getTypeName() {
		return 'com.woltlab.wcf.user';
	}
	
	/**
	 * Returns the ids of the users whose edits can be reverted.
	 * 
	 * @return	array<integer>
	 */
	protected function validateRevertContentChanges() {
		if (!MODULE_EDIT_HISTORY) {
			return array();
		}
		
		// check permissions
		if (!WCF::getSession()->getPermission('admin.content.canBulkRevertContentChanges')) {
			return array();
		}
		
		$userIDs = array();
		foreach ($this->objects as $user) {
			$userIDs[] = $user->userID;
		}
		
		return $userIDs;
	}
}
