<?php

namespace wcf\system\view\filter;

use wcf\data\DatabaseObjectList;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\IntegerFormField;

/**
 * Filter for columns that contain object ids.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class ObjectIdFilter extends AbstractFilter
{
    #[\Override]
    public function getFormField(): AbstractFormField
    {
        return IntegerFormField::create($this->id)
            ->label($this->languageItem)
            ->minimum(1)
            ->nullable();
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $list, string $value): void
    {
        $columnName = $this->getDatabaseColumnName($list);

        $list->getConditionBuilder()->add("{$columnName} = ?", [$value]);
    }
}
