<?php
namespace wcf\data;

/**
 * Every object action that provides popover previews for database objects has to implement this interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 * @since	5.3
 */
interface IPopoverAction {
	/**
	 * Validates the `getPopover` action.
	 */
	public function validateGetPopover();
	
	/**
	 * Returns the requested popover for a specific object.
	 * 
	 * Return value:
	 * 	[
	 * 		'template' => '...'
	 * 	]
	 * 
	 * @return	string[]
	 */
	public function getPopover();
}
