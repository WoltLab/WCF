<?php

namespace wcf\system\condition\type\user;

use wcf\data\DatabaseObjectList;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\condition\type\AbstractConditionType;
use wcf\system\condition\type\IDatabaseObjectListConditionType;
use wcf\system\condition\type\IObjectConditionType;
use wcf\system\form\builder\field\TextConditionFormField;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @implements IDatabaseObjectListConditionType<UserList<User>>
 * @implements IObjectConditionType<User>
 * @extends AbstractConditionType<string>
 */
final class UserUsernameConditionType extends AbstractConditionType implements IDatabaseObjectListConditionType, IObjectConditionType
{
    #[\Override]
    public function getFormField(string $id): TextConditionFormField
    {
        return TextConditionFormField::create($id)
            ->conditions($this->getConditions());
    }

    #[\Override]
    public function getIdentifier(): string
    {
        return 'username';
    }

    #[\Override]
    public function getLabel(): string
    {
        return 'wcf.condition.user.username';
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $objectList): void
    {
        ["condition" => $condition, "value" => $value] = @\unserialize($this->filter);
        $filter = match ($condition) {
            "%_" => $value . '%',
            "%_%" => '%' . $value . '%',
            "_%" => '%' . $value,
            default => '',
        };

        $objectList->getConditionBuilder()->add(
            $objectList->getDatabaseTableAlias() . '.username LIKE ?',
            [$filter]
        );
    }

    #[\Override]
    public function match(object $object): bool
    {
        ["condition" => $condition, "value" => $value] = @\unserialize($this->filter);

        return match ($condition) {
            "%_" => \str_starts_with($object->username, $value),
            "%_%" => \str_contains($object->username, $value),
            "_%" => \str_ends_with($object->username, $value),
            default => false,
        };
    }

    /**
     * @return array<string, string>
     */
    private function getConditions(): array
    {
        return [
            "%_" => "wcf.condition.startsWith",
            "%_%" => "wcf.condition.user.contains",
            "_%" => "wcf.condition.user.endsWith",
        ];
    }
}
