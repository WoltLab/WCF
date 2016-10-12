<?php
namespace wcf\data;

/**
 * Default interface for actions implementing message inline editing.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 */
interface IMessageInlineEditorAction {
	/**
	 * Provides WYSIWYG editor for message inline editing.
	 * 
	 * @return	array
	 */
	public function beginEdit();
	
	/**
	 * Saves changes made to a message.
	 * 
	 * @return	array
	 */
	public function save();
	
	/**
	 * Validates parameters to begin message inline editing.
	 */
	public function validateBeginEdit();
	
	/**
	 * Validates parameters to save changes made to a message.
	 */
	public function validateSave();
}
