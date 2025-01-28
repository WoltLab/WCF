<?php

namespace wcf\system\gridView\filter;

use wcf\data\DatabaseObjectList;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\IntegerFormField;

/**
 * Filter for columns that contain object ids.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class ObjectIdFilter extends AbstractFilter
{
    #[\Override]
    public function getFormField(string $id, string $label): AbstractFormField
    {
        return IntegerFormField::create($id)
            ->label($label)
            ->minimum(1)
            ->nullable();
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $list, string $id, string $value): void
    {
        $columnName = $this->getDatabaseColumnName($list, $id);

        $list->getConditionBuilder()->add("{$columnName} = ?", [$value]);
    }
}
