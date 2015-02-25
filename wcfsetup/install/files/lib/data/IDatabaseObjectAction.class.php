<?php
namespace wcf\data;

/**
 * Default interface for DatabaseObject-related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category	Community Framework
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
	 * @return	array<integer>
	 */
	public function getObjectIDs();
	
	/**
	 * Returns action-related parameters.
	 * 
	 * @return	array<array>
	 */
	public function getParameters();
	
	/**
	 * Returns results returned by active action.
	 * 
	 * @return	mixed
	 */
	public function getReturnValues();
}
