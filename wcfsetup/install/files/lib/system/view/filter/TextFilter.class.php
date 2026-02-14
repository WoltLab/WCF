<?php

namespace wcf\system\view\filter;

use wcf\data\DatabaseObjectList;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\WCF;

/**
 * Filter for text columns.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class TextFilter extends AbstractFilter
{
    #[\Override]
    public function getFormField(): AbstractFormField
    {
        return TextFormField::create($this->id)
            ->label($this->languageItem);
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $list, string $value): void
    {
        $columnName = $this->getDatabaseColumnName($list);

        $list->getConditionBuilder()->add(
            "{$columnName} LIKE ?",
            ['%' . WCF::getDB()->escapeLikeValue($value) . '%']
        );
    }
}
