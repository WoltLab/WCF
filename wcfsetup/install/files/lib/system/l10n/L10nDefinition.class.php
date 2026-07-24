<?php

namespace wcf\system\l10n;

/**
 * Describes the localized (`*_l10n`) table of a content type.
 *
 * The localized table stores one row per object and language with the fixed
 * skeleton columns `objectColumnName` (referencing the primary table),
 * `languageID` (`NULL` for monolingual content) followed by
 * the localized payload columns.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class L10nDefinition
{
    /**
     * @param string $primaryTableName name of the primary table
     * @param string $l10nTableName name of the localized table
     * @param string $objectColumnName name of the primary key column shared by both tables
     * @param list<string> $columnNames names of the localized payload columns
     */
    public function __construct(
        public readonly string $primaryTableName,
        public readonly string $l10nTableName,
        public readonly string $objectColumnName,
        public readonly array $columnNames,
    ) {
        if (!\str_ends_with($l10nTableName, '_l10n')) {
            throw new \InvalidArgumentException(
                "The localized table name '{$l10nTableName}' must use the '_l10n' suffix."
            );
        }

        if ($columnNames === []) {
            throw new \InvalidArgumentException('At least one localized column must be defined.');
        }
    }
}
