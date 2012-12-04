<?php
namespace wcf\data;

/**
 * Default interface for sortable database objects.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
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
