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
 * A definition may support the synchronization with language variables. In that
 * case the primary table carries an `identifierColumnName` column holding the
 * base name of a language variable (e.g. `wcf.user.option.birthdayShowYear`) or
 * `NULL` if the object is not linked to a language variable. The effective name
 * of the language variable of a payload column is the identifier concatenated
 * with the column's suffix from `languageItemSuffixes`. Sync-capable localized
 * tables additionally carry an `isPristine` column, see `L10nStorage`.
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
     * @param ?string $identifierColumnName name of the primary table column holding the language variable base name, `null` if the definition does not support the synchronization with language variables
     * @param array<string, string> $languageItemSuffixes maps each payload column to the suffix appended to the identifier to form its language variable name
     */
    public function __construct(
        public readonly string $primaryTableName,
        public readonly string $l10nTableName,
        public readonly string $objectColumnName,
        public readonly array $columnNames,
        public readonly ?string $identifierColumnName = null,
        public readonly array $languageItemSuffixes = [],
    ) {
        if (!\str_ends_with($l10nTableName, '_l10n')) {
            throw new \InvalidArgumentException(
                "The localized table name '{$l10nTableName}' must use the '_l10n' suffix."
            );
        }

        if ($columnNames === []) {
            throw new \InvalidArgumentException('At least one localized column must be defined.');
        }

        if ($identifierColumnName !== null) {
            foreach ($columnNames as $columnName) {
                if (!\array_key_exists($columnName, $languageItemSuffixes)) {
                    throw new \InvalidArgumentException(
                        "Missing language variable suffix for the localized column '{$columnName}'."
                    );
                }
            }
        }
    }

    /**
     * Returns whether this definition supports the synchronization of its
     * localized values with language variables.
     */
    public function supportsLanguageItemSync(): bool
    {
        return $this->identifierColumnName !== null;
    }
}
