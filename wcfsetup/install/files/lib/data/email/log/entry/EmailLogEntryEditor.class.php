<?php

namespace wcf\data\email\log\entry;

use wcf\data\DatabaseObjectEditor;

/**
 * Extends the email log entry object with functions to create, update and delete history entries.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated  6.3 Use `EmailLogEntryBuilder` instead.
 *
 * @mixin       EmailLogEntry
 * @extends DatabaseObjectEditor<EmailLogEntry>
 */
class EmailLogEntryEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = EmailLogEntry::class;
}
