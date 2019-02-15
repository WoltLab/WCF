<?php
namespace wcf\data;

/**
 * Represents objects that support some of their properties to be saved.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 * @since	3.1
 */
interface IVersionTrackerObject extends IUserContent {
	/**
	 * Returns the link to the object's edit page.
	 * 
	 * @return      string
	 */
	public function getEditLink();
	
	/**
	 * Returns the object's unique id.
	 * 
	 * @return      integer
	 */
	public function getObjectID();
}
