<?php
namespace wcf\system\message\embedded\object;
use wcf\data\DatabaseObject;

/**
 * Default interface of embedded object handler.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.message.embedded.object
 * @category	Community Framework
 */
interface IMessageEmbeddedObjectHandler {
	/**
	 * Parses the given message to extract embedded objects.
	 * Returns the IDs of found embedded objects.
	 * 
	 * @param	string		$message
	 * @return	integer[]
	 */
	public function parseMessage($message);
	
	/**
	 * Loads and returns embedded objects.
	 * 
	 * @param	array		$objectIDs
	 * @return	DatabaseObject[]
	 */
	public function loadObjects(array $objectIDs);
}
