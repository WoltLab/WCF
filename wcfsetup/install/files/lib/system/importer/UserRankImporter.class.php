<?php
namespace wcf\system\importer;
use wcf\data\user\group\UserGroup;
use wcf\data\user\rank\UserRankAction;

/**
 * Imports user ranks.
 *
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class UserRankImporter implements IImporter {
	/**
	 * @see wcf\system\importer::import()
	 */
	public function import($oldID, array $data) {
		$data['groupID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user.group', $data['groupID']);
		if (!$data['groupID']) $data['groupID'] = UserGroup::getGroupByType(UserGroup::USERS)->groupID;
		
		$action = new UserRankAction(array(), 'create', array(
			'data' => $data		
		));
		$returnValues = $action->executeAction();
		$newID = $returnValues['returnValues']->rankID;
		
		ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.user.rank', $oldID, $newID);
		
		return $newID;
	}
}
