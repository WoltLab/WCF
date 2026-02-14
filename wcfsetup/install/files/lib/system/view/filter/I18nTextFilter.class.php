<?php

namespace wcf\system\view\filter;

use wcf\data\DatabaseObjectList;
use wcf\system\WCF;

/**
 * Filter for text columns that are using i18n phrases.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class I18nTextFilter extends TextFilter
{
    #[\Override]
    public function applyFilter(DatabaseObjectList $list, string $value): void
    {
        $columnName = $this->getDatabaseColumnName($list);

        $list->getConditionBuilder()->add("({$columnName} LIKE ? OR {$columnName} IN (SELECT languageItem FROM wcf1_language_item WHERE languageID = ? AND languageItemValue LIKE ?))", [
            '%' . WCF::getDB()->escapeLikeValue($value) . '%',
            WCF::getLanguage()->languageID,
            '%' . WCF::getDB()->escapeLikeValue($value) . '%'
        ]);
    }
}
