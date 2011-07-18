<?php
namespace wcf\data\cronjob;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\WCF;

/**
 * Executes cronjob-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.cronjob
 * @category 	Community Framework
 */
class CronjobAction extends AbstractDatabaseObjectAction {
	/**
	 * @see wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\cronjob\CronjobEditor';
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsCreate
	 */
	protected $permissionsCreate = array('admin.system.cronjobs.canAddCronjob');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.system.cronjobs.canDeleteCronjob');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.system.cronjobs.canEditCronjob');
	
	/**
	 * Validates permissions and parameters
	 */
	public function validateDelete() {
		parent::validateDelete();
		
		foreach ($this->objects as $cronjob) {
			if (!$cronjob->isDeletable()) {
				throw new ValidateActionException('Insufficient permissions');
			} 
		}
	}
	
	/**
	 * Validates permissions and parameters
	 */
	public function validateUpdate() {
		parent::validateUpdate();
		
		foreach ($this->objects as $cronjob) {
			if (!$cronjob->isEditable()) {
				throw new ValidateActionException('Insufficient permissions');
			} 
		}
	}
	
	/**
	 * Validates permissions and parameters
	 */
	public function validateToggle() {
		parent::validateUpdate();
		
		foreach ($this->objects as $cronjob) {
			if (!$cronjob->canBeDisabled()) {
				throw new ValidateActionException('Insufficient permissions');
			} 
		}
	}
	
	/**
	 * Toggles status.
	 */
	public function toggle() {
		foreach ($this->objects as $cronjob) {
			$newStatus = ($cronjob->active) ? 0 : 1;
			$cronjob->update(array('active' => $newStatus));
		}
	}
	
	/**
	 * Validates permissions and parameters
	 */
	public function validateExecute() {
		parent::validateUpdate();
	}
	
	/**
	 * Executes cronjobs.
	 */
	public function execute() {
		// TODO: implement me
		foreach ($this->objects as $cronjob) {
			
		}
	}
}
