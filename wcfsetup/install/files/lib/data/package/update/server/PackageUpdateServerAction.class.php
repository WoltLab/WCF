<?php
namespace wcf\data\package\update\server;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\data\TDatabaseObjectToggle;
use wcf\system\exception\PermissionDeniedException;

/**
 * Executes package update server-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Package\Update\Server
 * 
 * @method	PackageUpdateServer		create()
 * @method	PackageUpdateServerEditor[]	getObjects()
 * @method	PackageUpdateServerEditor	getSingleObject()
 */
class PackageUpdateServerAction extends AbstractDatabaseObjectAction implements IToggleAction {
	use TDatabaseObjectToggle;
	
	/**
	 * @inheritDoc
	 */
	protected $className = PackageUpdateServerEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsCreate = ['admin.configuration.package.canEditServer'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.configuration.package.canEditServer'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.configuration.package.canEditServer'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['create', 'delete', 'toggle', 'update'];
	
	/**
	 * @inheritDoc
	 */
	public function validateDelete() {
		parent::validateDelete();
		
		/** @var PackageUpdateServer $updateServer */
		foreach ($this->getObjects() as $updateServer) {
			if (!$updateServer->canDelete()) throw new PermissionDeniedException();
		}
	}
}
