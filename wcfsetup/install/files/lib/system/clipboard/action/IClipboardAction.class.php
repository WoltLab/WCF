<?php
namespace wcf\system\clipboard\action;

/**
 * Basic interface for all clipboard editor actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.clipboard.action
 * @category 	Community Framework
 */
interface IClipboardAction {
	/**
	 * Returns type name identifier.
	 * 
	 * @return	string
	 */
	public function getTypeName();
	
	/**
	 * Returns action data, return NULL if action is not applicable.
	 * 
	 * @param	array		$objects
	 * @param	string		$actionName
	 * @return	wcf\system\clipboard\ClipboardEditorItem
	 */
	public function execute(array $objects, $actionName);
	
	/**
	 * Returns label for item editor.
	 * 
	 * @param	array		$objects
	 * @return	string
	 */
	public function getEditorLabel(array $objects);
}
