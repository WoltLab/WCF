<?php
namespace wcf\data;

/**
 * Every database object action whose objects represent a collapsible container
 * whose content can be loaded via AJAX has to implement this interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 */
interface ILoadableContainerAction {
	/**
	 * Toggles the container state of the relevant objects and loads their
	 * content if necessary.
	 */
	public function loadContainer();
	
	/**
	 * Validates the 'loadContainer' action.
	 */
	public function validateLoadContainer();
}
