<?php
namespace wcf\system\importer;
use wcf\data\user\group\UserGroup;
use wcf\data\user\group\UserGroupAction;

/**
 * Imports user groups.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class UserGroupImporter extends AbstractImporter {
	/**
	 * @see	\wcf\system\importer\AbstractImporter::$className
	 */
	protected $className = 'wcf\data\user\group\UserGroup';
	
	/**
	 * @see	\wcf\system\importer\IImporter::import()
	 */
	public function import($oldID, array $data, array $additionalData = array()) {
		if ($data['groupType'] < 4) {
			$newGroupID = UserGroup::getGroupByType($data['groupType'])->groupID;
		}
		else {
			$action = new UserGroupAction(array(), 'create', array(
				'data' => $data
			));
			$returnValues = $action->executeAction();
			$newGroupID = $returnValues['returnValues']->groupID;
		}
		
		ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.user.group', $oldID, $newGroupID);
		
		return $newGroupID;
	}
}
