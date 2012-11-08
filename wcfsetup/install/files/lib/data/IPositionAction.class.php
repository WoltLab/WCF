<?php
namespace wcf\data;

/**
 * Every database object action whose objects can be positioned via AJAX has to
 * implement this interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category	Community Framework
 */
interface IPositionAction {
	/**
	 * Updates the positions of the relevant objects.
	 */
	public function updatePosition();
	
	/**
	 * Validates the "updatePosition" action.
	 */
	public function validateUpdatePosition();
}
