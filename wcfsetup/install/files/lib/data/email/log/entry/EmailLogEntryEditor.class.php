<?php

namespace wcf\data\email\log\entry;

use wcf\data\DatabaseObjectEditor;

/**
 * Extends the email log entry object with functions to create, update and delete history entries.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\Email\Log\Entry
 *
 * @method static EmailLogEntry    create(array $parameters = [])
 * @method      EmailLogEntry    getDecoratedObject()
 * @mixin       EmailLogEntry
 */
class EmailLogEntryEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = EmailLogEntry::class;
}
