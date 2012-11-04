<?php
namespace wcf\data\application\group;
use wcf\data\application\ApplicationAction;
use wcf\data\application\ApplicationList;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes application group-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.application.group
 * @category	Community Framework
 */
class ApplicationGroupAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\application\group\ApplicationGroupEditor';
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.system.canManageApplication');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::create()
	 */
	public function create() {
		$applicationGroup = parent::create();
		
		if (isset($this->parameters['applications'])) {
			$applicationAction = new ApplicationAction($this->parameters['applications'], 'group', array('groupID' => $applicationGroup->groupID));
			$applicationAction->executeAction();
		}
		
		return $applicationGroup;
	}
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::delete()
	 */
	public function delete() {
		$groupIDs = array();
		foreach ($this->objects as $applicationGroup) {
			$groupIDs[] = $applicationGroup->groupID;
		}
		
		// read all applications associated by affected groups
		$applicationList = new ApplicationList();
		$applicationList->getConditionBuilder()->add("application.groupID IN (?)", array($groupIDs));
		$applicationList->sqlLimit = 0;
		$applicationList->readObjects();
		
		$applicationAction = new ApplicationAction($applicationList->getObjects(), 'ungroup');
		$applicationAction->executeAction();
		
		return parent::delete();
	}
}
