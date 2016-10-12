<?php
namespace wcf\system\edit;
use wcf\data\object\type\IObjectTypeProvider;
use wcf\system\exception\PermissionDeniedException;

/**
 * Represents an object which edit history can be saved.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Edit
 */
interface IHistorySavingObjectTypeProvider extends IObjectTypeProvider {
	/**
	 * Checks the permissions to review the edit history and to revert to an
	 * older version of the given IHistorySavingObject.
	 * 
	 * @param	IHistorySavingObject	$object
	 * @throws	PermissionDeniedException	if access is denied
	 * @throws	\InvalidArgumentException	if given object has not be provided by this provider and thus cannot be checked by this method
	 */
	public function checkPermissions(IHistorySavingObject $object);
	
	/**
	 * Returns the identifier of the appropriate page menu item.
	 * 
	 * @return	string
	 * @deprecated  3.0
	 */
	public function getActivePageMenuItem();
}
