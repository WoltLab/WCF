<?php
namespace wcf\data\object\type;
use wcf\data\DatabaseObject;

/**
 * Any object type provider should implement this interface.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Object\Type
 */
interface IObjectTypeProvider {
	/**
	 * Gets an object by its ID.
	 * 
	 * @param	integer		$objectID
	 * @return	\wcf\data\DatabaseObject
	 */
	public function getObjectByID($objectID);
	
	/**
	 * Gets like objects by their IDs.
	 * 
	 * @param	integer[]		$objectIDs
	 * @return	DatabaseObject[]
	 */
	public function getObjectsByIDs(array $objectIDs);
}
