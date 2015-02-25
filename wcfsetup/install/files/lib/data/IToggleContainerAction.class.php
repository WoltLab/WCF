<?php
namespace wcf\data;

/**
 * Every database object action whose objects represent a collapsible container
 * has to implement this interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category	Community Framework
 */
interface IToggleContainerAction {
	/**
	 * Toggles the container state of the relevant objects.
	 */
	public function toggleContainer();
	
	/**
	 * Validates the 'toggleContainer' action.
	 */
	public function validateToggleContainer();
}
