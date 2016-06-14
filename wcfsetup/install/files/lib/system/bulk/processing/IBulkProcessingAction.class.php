<?php
namespace wcf\system\bulk\processing;
use wcf\data\DatabaseObjectList;

/**
 * Every bulk processing action has to implement this interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bulk\Processing
 * @since	3.0
 */
interface IBulkProcessingAction {
	/**
	 * Executes the bulk processing action on all objects in the given object
	 * list.
	 * 
	 * @param	\wcf\data\DatabaseObjectList		$objectList
	 */
	public function executeAction(DatabaseObjectList $objectList);
	
	/**
	 * Returns the output for setting additional action parameters.
	 * 
	 * @return	string
	 */
	public function getHTML();
	
	/**
	 * Returns an object list which will be populated with conditions to read
	 * the processed objects.
	 * 
	 * @return	\wcf\data\DatabaseObjectList
	 */
	public function getObjectList();
	
	/**
	 * Returns true if the action is available for the active user.
	 * 
	 * @return	boolean
	 */
	public function isAvailable();
	
	/**
	 * Reads additional parameters to execute the action.
	 */
	public function readFormParameters();
	
	/**
	 * Resets the internally stored additional action parameters.
	 */
	public function reset();
	
	/**
	 * Validates the additional action parameters.
	 */
	public function validate();
}
