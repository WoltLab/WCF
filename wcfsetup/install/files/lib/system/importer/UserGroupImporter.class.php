<?php
namespace wcf\system\importer;
use wcf\data\user\group\UserGroupAction;

/**
 * Imports user groups.
 *
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class UserGroupImporter implements IImporter {
	/**
	 * @see wcf\system\importer\IImporter::import()
	 */
	public function import($oldID, array $data) {
		$action = new UserGroupAction(array(), 'create', array(
			'data' => $data		
		));
		$returnValues = $action->executeAction();
		$newGroupID = $returnValues['returnValues']->groupID;
		
		ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.user.group', $oldID, $newGroupID);
		
		return $newGroupID;
	}
}
