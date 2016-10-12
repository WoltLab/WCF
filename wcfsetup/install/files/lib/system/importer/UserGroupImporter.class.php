<?php
namespace wcf\system\importer;
use wcf\data\user\group\UserGroup;
use wcf\data\user\group\UserGroupAction;

/**
 * Imports user groups.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Importer
 */
class UserGroupImporter extends AbstractImporter {
	/**
	 * @inheritDoc
	 */
	protected $className = UserGroup::class;
	
	/**
	 * @inheritDoc
	 */
	public function import($oldID, array $data, array $additionalData = []) {
		if ($data['groupType'] < 4) {
			$newGroupID = UserGroup::getGroupByType($data['groupType'])->groupID;
		}
		else {
			$action = new UserGroupAction([], 'create', [
				'data' => $data
			]);
			$returnValues = $action->executeAction();
			$newGroupID = $returnValues['returnValues']->groupID;
		}
		
		ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.user.group', $oldID, $newGroupID);
		
		return $newGroupID;
	}
}
