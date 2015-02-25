<?php
namespace wcf\data\object\type;

/**
 * Any object type provider should implement this interface.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.object.type
 * @category	Community Framework
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
	 * @param	array<integer>		$objectIDs
	 * @return	array<\wcf\data\DatabaseObject>
	 */
	public function getObjectsByIDs(array $objectIDs);
}
