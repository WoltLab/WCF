<?php
namespace wcf\data\package\update\server;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes package update server-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package.update.server
 * @category 	Community Framework
 */
class PackageUpdateServerAction extends AbstractDatabaseObjectAction {
	/**
	 * @see wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\package\update\server\PackageUpdateServerEditor';
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsCreate
	 */
	protected $permissionsCreate = array('admin.system.package.canEditServer');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.system.package.canEditServer');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.system.package.canEditServer');
	
	/**
	 * Validates permissions and parameters
	 */
	public function validateToggle() {
		parent::validateUpdate();
	}
	
	/**
	 * Toggles status.
	 */
	public function toggle() {
		foreach ($this->objects as $server) {
			$server->update(array('disabled' => ($server->disabled) ? 0 : 1));
		}
	}
}
