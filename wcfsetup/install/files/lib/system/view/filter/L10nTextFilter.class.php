<?php

namespace wcf\system\view\filter;

use wcf\data\DatabaseObjectList;
use wcf\system\l10n\L10nDefinition;
use wcf\system\WCF;

/**
 * Filter for localized text columns that are stored in the `_l10n` table of
 * the database object.
 *
 * The filter matches the rows of the requested language and the monolingual
 * rows. It uses a subquery instead of a join so that it also works for
 * `DatabaseObjectList::countObjects()`.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
class L10nTextFilter extends TextFilter
{
    public function __construct(
        private readonly L10nDefinition $definition,
        private readonly string $columnName,
        string $id,
        string $languageItem,
    ) {
        parent::__construct($id, $languageItem);

        if (!\in_array($columnName, $definition->columnNames, true)) {
            throw new \InvalidArgumentException("Unknown localized column '{$columnName}'.");
        }
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $list, string $value): void
    {
        $objectColumn = $list->getDatabaseTableAlias() . '.' . $this->definition->objectColumnName;

        $list->getConditionBuilder()->add(
            "{$objectColumn} IN (
                SELECT  {$this->definition->objectColumnName}
                FROM    {$this->definition->l10nTableName}
                WHERE   (languageID = ? OR languageID IS NULL)
                    AND {$this->columnName} LIKE ?
            )",
            [
                WCF::getLanguage()->languageID,
                '%' . WCF::getDB()->escapeLikeValue($value) . '%'
            ]
        );
    }
}
