<?php
namespace wcf\data\user\group;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes user group-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2010 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.group
 * @category 	Community Framework
 */
class UserGroupAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$className
	 */
	public $className = 'wcf\data\user\group\UserGroupEditor';
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsCreate
	 */
	protected $permissionsCreate = array('admin.user.canAddGroup');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.user.canDeleteGroup');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.user.canEditGroup');
	
	/**
	 * Creates a new group.
	 * 
	 * @return	UserGroup
	 */
	public function create() {
		$group = parent::create();
		
		$groupEditor = new UserGroupEditor($group);
		$groupEditor->updateGroupOptions($this->parameters['options']);
		
		return $group;
	}
	
	/**
	 * Updates groups.
	 */
	public function update() {
		if (!count($this->objects)) {
			$this->readObjects();
		}
		
		foreach ($this->objects as $object) {
			$object->update($this->parameters['data']);
			$object->updateGroupOptions($this->parameters['options']);
		}
	}
}
