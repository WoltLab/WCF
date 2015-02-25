<?php
namespace wcf\data;

/**
 * Every object action whose objects can be sorted via AJAX has to implement this
 * interface.
 * 
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category	Community Framework
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
