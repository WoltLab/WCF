<?php

namespace wcf\system\condition\type\user;

use wcf\data\DatabaseObjectList;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\condition\type\IDatabaseObjectListConditionType;
use wcf\system\condition\type\IObjectConditionType;
use wcf\system\form\builder\field\AbstractConditionFormField;
use wcf\system\form\builder\field\DateConditionFormField;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @implements IDatabaseObjectListConditionType<UserList<User>>
 * @implements IObjectConditionType<User>
 */
final class RegistrationDateConditionType implements IDatabaseObjectListConditionType, IObjectConditionType
{
    #[\Override]
    public function getFormField(string $id): AbstractConditionFormField
    {
        return DateConditionFormField::create($id)
            ->conditions(\array_combine($this->getConditions(), $this->getConditions()))
            ->nullable()
            ->supportTime();
    }

    #[\Override]
    public function getIdentifier(): string
    {
        return 'registrationDate';
    }

    #[\Override]
    public function getLabel(): string
    {
        return 'wcf.condition.user.registrationDate';
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $objectList, float|int|string $filter): void
    {
        // TODO
    }

    #[\Override]
    public function match(object $object, float|int|string $filter): bool
    {
        // TODO
        return false;
    }

    /**
     * @return string[]
     */
    private function getConditions(): array
    {
        return [">", "<", ">=", "<="];
    }
}
