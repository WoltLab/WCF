<?php
namespace wcf\system\clipboard\action;
use wcf\data\clipboard\action\ClipboardAction;
use wcf\data\DatabaseObject;

/**
 * Basic interface for all clipboard editor actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Clipboard\Action
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
	 * @param	DatabaseObject[]				$objects
	 * @param	\wcf\data\clipboard\action\ClipboardAction	$action
	 * @return	\wcf\system\clipboard\ClipboardEditorItem
	 */
	public function execute(array $objects, ClipboardAction $action);
	
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
