<?php
namespace wcf\data;

/**
 * Every object action whose objects can be sorted via AJAX has to implement this
 * interface.
 * 
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 */
interface ISortableAction {
	/**
	 * Validates the 'updatePosition' action.
	 */
	public function validateUpdatePosition();
	
	/**
	 * Updates the position of given objects.
	 */
	public function updatePosition();
}
