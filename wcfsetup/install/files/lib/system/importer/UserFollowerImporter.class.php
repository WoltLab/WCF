<?php
namespace wcf\system\importer;
use wcf\data\user\follow\UserFollowAction;

/**
 * Imports followers.
 *
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class UserFollowerImporter implements IImporter {
	/**
	 * @see wcf\system\importer\IImporter::import()
	 */
	public function import($oldID, array $data, array $additionalData = array()) {
		$data['userID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID']);
		$data['followUserID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['followUserID']);
		if (!$data['userID'] || !$data['followUserID']) return 0;
		
		$action = new UserFollowAction(array(), 'create', array(
			'data' => $data		
		));
		$returnValues = $action->executeAction();
		return $returnValues['returnValues']->followID;
	}
}
