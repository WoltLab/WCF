<?php
namespace wcf\data\edit\history\entry;
use wcf\data\DatabaseObjectEditor;

/**
 * Extends the edit history entry object with functions to create, update and delete history entries.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.edit.history.entry
 * @category	Community Framework
 */
class EditHistoryEntryEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\DatabaseObjectEditor::$baseClass
	 */
	protected static $baseClass = 'wcf\data\edit\history\entry\EditHistoryEntry';
}
