<?php
namespace wcf\data;

/**
 * Default interface for DatabaseObject-related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 */
interface IDatabaseObjectAction {
	/**
	 * Executes the previously chosen action.
	 */
	public function executeAction();
	
	/**
	 * Validates action-related parameters.
	 */
	public function validateAction();
	
	/**
	 * Returns active action name.
	 * 
	 * @return	string
	 */
	public function getActionName();
	
	/**
	 * Returns DatabaseObject-related object ids.
	 * 
	 * @return	integer[]
	 */
	public function getObjectIDs();
	
	/**
	 * Returns action-related parameters.
	 * 
	 * @return	mixed[]
	 */
	public function getParameters();
	
	/**
	 * Returns results returned by active action.
	 * 
	 * @return	mixed
	 */
	public function getReturnValues();
}
