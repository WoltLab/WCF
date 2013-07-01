<?php
namespace wcf\system\importer;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\system\database\DatabaseException;
use wcf\system\WCF;

/**
 * Imports users.
 *
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class UserImporter implements IImporter {
	/**
	 * @see wcf\system\importer::import()
	 */
	public function import($oldID, array $data) {
		// check existing user id
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_user
			WHERE	userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($oldID));
		$row = $statement->fetchArray();
		if (!$row['count']) $data['userID'] = $oldID;
		
		// get group ids
		$groupIDs = array();
		if (isset($data['groupIDs'])) {
			foreach ($data['groupIDs'] as $oldGroupID) {
				$newGroupID = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user.group', $oldGroupID);
				if ($newGroupID) $groupIDs[] = $newGroupID;
			}
			
			unset($data['groupIDs']);
		}
		
		// handle user options
		$userOptions = array();
		if (isset($data['options'])) {
			foreach ($data['options'] as $optionName => $optionValue) {
				if (is_int($optionName)) $optionID = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user.option', $optionName);
				else $optionID = User::getUserOptionID($optionName);
				
				if ($optionID) {
					$userOptions[$optionID] = $optionValue;
				}
			}
			
			unset($data['options']);
		}
		
		// create user
		$action = new UserAction(array(), 'create', array(
			'data' => $data,
			'groups' => $groupIDs,
			'options' => $userOptions
		));
		$returnValues = $action->executeAction();
		
		$newUserID = $returnValues['returnValues']->userID;
		ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.user', $oldID, $newUserID);
		
		return $newUserID;
	}
}
