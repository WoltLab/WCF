<?php

namespace wcf\data\edit\history\entry;

use wcf\data\DatabaseObjectEditor;

/**
 * Extends the edit history entry object with functions to create, update and delete history entries.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated  6.3 Use `EditHistoryEntryBuilder` instead.
 *
 * @mixin       EditHistoryEntry
 * @extends DatabaseObjectEditor<EditHistoryEntry>
 */
class EditHistoryEntryEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = EditHistoryEntry::class;
}
