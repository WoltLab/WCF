<?php

namespace wcf\system\gridView\filter;

use wcf\data\DatabaseObjectList;
use wcf\system\cache\runtime\UserRuntimeCache;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\user\UserFormField;
use wcf\system\gridView\filter\exception\InvalidFilterValue;

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
        $user = UserRuntimeCache::getInstance()->getObject((int)$value);
        if ($user === null) {
            throw new InvalidFilterValue("Invalid value '{$value}' for filter '{$id}' given.");
        }

        $columnName = $this->getDatabaseColumnName($list, $id);

        $list->getConditionBuilder()->add("{$columnName} = ?", [$user->userID]);
    }

    #[\Override]
    public function renderValue(string $value): string
    {
        $user = UserRuntimeCache::getInstance()->getObject((int)$value);

        return $user ? $user->username : '';
    }
}
