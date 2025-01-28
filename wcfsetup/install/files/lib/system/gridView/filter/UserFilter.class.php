<?php

namespace wcf\system\gridView\filter;

use wcf\data\DatabaseObjectList;
use wcf\system\cache\runtime\UserRuntimeCache;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\user\UserFormField;

/**
 * Filter for columns that contain user ids.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class UserFilter extends AbstractFilter
{
    #[\Override]
    public function getFormField(string $id, string $label): AbstractFormField
    {
        return UserFormField::create($id)
            ->label($label)
            ->nullable();
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $list, string $id, string $value): void
    {
        $columnName = $this->getDatabaseColumnName($list, $id);

        $list->getConditionBuilder()->add("{$columnName} = ?", [$value]);
    }

    #[\Override]
    public function renderValue(string $value): string
    {
        $user = UserRuntimeCache::getInstance()->getObject($value);

        return $user ? $user->username : '';
    }
}
