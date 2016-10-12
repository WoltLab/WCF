<?php
namespace wcf\data;

/**
 * Every database object action whose objects can be toggled has to implement this
 * interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 */
interface IToggleAction {
	/**
	 * Toggles the "isDisabled" status of the relevant objects.
	 */
	public function toggle();
	
	/**
	 * Validates the "toggle" action.
	 */
	public function validateToggle();
}
