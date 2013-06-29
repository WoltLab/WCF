<?php
namespace wcf\system\importer;
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
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_user
			WHERE	userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($oldID));
		$row = $statement->fetchArray();
		if (!$row['count']) $data['userID'] = $oldID;
		
		$action = new UserAction(array(), 'create', array(
			'data' => $data
		));
		$returnValues = $action->executeAction();
		
		$newUserID = $returnValues['returnValues']->groupID;
		ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.user', $oldID, $newUserID);
		
		return $newUserID;
	}
}
