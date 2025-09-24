<?php

namespace wcf\system\view\filter;

use wcf\data\DatabaseObjectList;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\CheckboxFormField;

/**
 * Filter for boolean columns.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class BooleanFilter extends AbstractFilter
{
    public function __construct(
        string $id,
        string $languageItem,
        string $databaseColumn = '',
        private readonly bool $reverseValue = false
    ) {
        parent::__construct($id, $languageItem, $databaseColumn);
    }

    #[\Override]
    public function getFormField(): AbstractFormField
    {
        return CheckboxFormField::create($this->id)
            ->label($this->languageItem)
            ->nullable();
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $list, string $value): void
    {
        $columnName = $this->getDatabaseColumnName($list);

        $list->getConditionBuilder()->add("{$columnName} = ?", [
            $this->reverseValue ? 0 : 1,
        ]);
    }

    #[\Override]
    public function renderValue(string $value): string
    {
        return '';
    }
}
