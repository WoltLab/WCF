<?php
namespace wcf\data\option;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes option-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
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
	protected $permissionsCreate = ['admin.configuration.canEditOption'];
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = ['admin.configuration.canEditOption'];
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = ['admin.configuration.canEditOption'];
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$requireACP
	 */
	protected $requireACP = ['create', 'delete', 'import', 'update', 'updateAll'];
	
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
		call_user_func([$this->className, 'import'], $this->parameters['data']);
	}
	
	/**
	 * Updates the value of all given options.
	 */
	public function updateAll() {
		// create data
		call_user_func([$this->className, 'updateAll'], $this->parameters['data']);
	}
}
