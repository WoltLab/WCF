<?php
namespace wcf\data;

/**
 * Represents objects that support some of their properties to be saved.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 */
interface IVersionTrackerObject extends IUserContent {
	/**
	 * Returns the object's unique id.
	 * 
	 * @return      integer
	 */
	public function getObjectID();
}
