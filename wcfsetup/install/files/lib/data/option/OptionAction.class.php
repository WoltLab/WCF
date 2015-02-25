<?php
namespace wcf\data\option;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes option-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.
 * @category	Community Framework
 */
class OptionAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\option\OptionEditor';
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsCreate
	 */
	protected $permissionsCreate = array('admin.system.canEditOption');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.system.canEditOption');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.system.canEditOption');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$requireACP
	 */
	protected $requireACP = array('create', 'delete', 'import', 'update', 'updateAll');
	
	/**
	 * Validates permissions and parameters.
	 */
	public function validateImport() {
		parent::validateCreate();
	}
	
	/**
	 * Validates permissions and parameters.
	 */
	public function validateUpdateAll() {
		parent::validateCreate();
	}
	
	/**
	 * Imports options.
	 */
	public function import() {
		// create data
		call_user_func(array($this->className, 'import'), $this->parameters['data']);
	}
	
	/**
	 * Updates the value of all given options.
	 */
	public function updateAll() {
		// create data
		call_user_func(array($this->className, 'updateAll'), $this->parameters['data']);
	}
}
