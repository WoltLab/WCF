<?php
namespace wcf\data\user;
use wcf\system\edit\EditHistoryManager;
use wcf\system\WCF;

/**
 * Executes actions on user generated content.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User
 */
class UserContentAction extends UserAction {
	/**
	 * Checks permissions to bulk revert.
	 */
	public function validateBulkRevert() {
		WCF::getSession()->checkPermissions('admin.content.canBulkRevertContentChanges');
	}
	
	/**
	 * Bulk reverts changes made to history saving objects.
	 */
	public function bulkRevert() {
		$this->readInteger('timeframe', true);
		if (!$this->parameters['timeframe']) {
			$this->parameters['timeframe'] = 86400 * 7;
		}
		
		EditHistoryManager::getInstance()->bulkRevert($this->objectIDs, $this->parameters['timeframe']);
	}
}
