<?php
namespace wcf\data\trophy;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Trophy related actions. 
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Trophy
 * @since	3.1
 */
class TrophyAction extends AbstractDatabaseObjectAction implements IToggleAction {
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.trophy.canManageTrophy'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['toggle', 'delete'];
	
	/**
	 * @inheritDoc
	 */
	public function toggle() {
		foreach ($this->getObjects() as $trophy) {
			/** @var TrophyEditor $trophy */
			$trophy->update(['isDisabled' => $trophy->isDisabled ? 0 : 1]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateToggle() {
		WCF::getSession()->checkPermissions(['admin.trophy.canManageTrophy']);
		
		// read objects
		if (empty($this->objects)) {
			$this->readObjects();
			
			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
	}
}
