<?php
namespace wcf\data;

/**
 * Default implementation of the `IToggleAction` interface.
 * 
 * @author	Florian Gail
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 * @since	5.2
 *
 * @mixin	AbstractDatabaseObjectAction
 */
trait TDatabaseObjectToggle {
	/**
	 * Validates the "toggle" action.
	 */
	public function validateToggle() {
		$this->validateUpdate();
	}
	
	/**
	 * Toggles the "isDisabled" status of the relevant objects.
	 */
	public function toggle() {
		foreach ($this->getObjects() as $object) {
			$object->update([
				'isDisabled' => $object->isDisabled ? 0 : 1
			]);
		}
	}
}
