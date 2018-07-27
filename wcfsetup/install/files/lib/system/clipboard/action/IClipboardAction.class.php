<?php
namespace wcf\system\clipboard\action;
use wcf\data\clipboard\action\ClipboardAction;
use wcf\data\DatabaseObject;
use wcf\system\clipboard\ClipboardEditorItem;

/**
 * Basic interface for all clipboard editor actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
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
	 * Returns the editor item for the clipboard action with the given name or `null`
	 * if the action is not applicable to the given objects.
	 * 
	 * @param	DatabaseObject[]	$objects
	 * @param	ClipboardAction		$action
	 * @return	ClipboardEditorItem|null
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
	
	/**
	 * Returns the list of action names that should trigger a page reload once they
	 * have been executed.
	 * 
	 * @return      string[]
	 * @since       3.2
	 */
	public function getReloadPageOnSuccess();
}
