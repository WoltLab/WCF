<?php
namespace wcf\system\clipboard\action;
use wcf\data\clipboard\action\ClipboardAction;

/**
 * Basic interface for all clipboard editor actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.clipboard.action
 * @category	Community Framework
 */
interface IClipboardAction {
	/**
	 * Returns type name identifier.
	 * 
	 * @return	string
	 */
	public function getTypeName();
	
	/**
	 * Returns editor item for the clipboard action with the given name or null
	 * if the action is not applicable to the given objects.
	 * 
	 * @param	array<\wcf\data\DatabaseObject>			$objects
	 * @param	\wcf\data\clipboard\action\ClipboardAction	$action
	 * @return	\wcf\system\clipboard\ClipboardEditorItem
	 */
	public function execute(array $objects, ClipboardAction $action);
	
	/**
	 * Filters the given objects by the given type data and returns the filtered
	 * list.
	 * 
	 * @param	array		$objects
	 * @param	array		$typeData
	 * @return	array
	 */
	public function filterObjects(array $objects, array $typeData);
	
	/**
	 * Returns action class name.
	 * 
	 * @return	string
	 */
	public function getClassName();
	
	/**
	 * Returns label for item editor.
	 * 
	 * @param	array		$objects
	 * @return	string
	 */
	public function getEditorLabel(array $objects);
}
