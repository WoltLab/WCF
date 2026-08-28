<?php

namespace wcf\data\blacklist\entry;

use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit blacklist entries.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated  6.3
 *
 * @mixin BlacklistEntry
 * @extends DatabaseObjectEditor<BlacklistEntry>
 * @since 5.2
 */
class BlacklistEntryEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    public static $baseClass = BlacklistEntry::class;
}
