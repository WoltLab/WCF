<?php
namespace wcf\data\edit\history\entry;
use wcf\data\DatabaseObjectEditor;

/**
 * Extends the edit history entry object with functions to create, update and delete history entries.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Edit\History\Entry
 * 
 * @method static	EditHistoryEntry	create(array $parameters = [])
 * @method		EditHistoryEntry	getDecoratedObject()
 * @mixin		EditHistoryEntry
 */
class EditHistoryEntryEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = EditHistoryEntry::class;
}
