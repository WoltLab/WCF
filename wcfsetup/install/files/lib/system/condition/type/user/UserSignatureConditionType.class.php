<?php

namespace wcf\system\condition\type\user;

use wcf\data\DatabaseObjectList;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\condition\type\AbstractConditionType;
use wcf\system\condition\type\IDatabaseObjectListConditionType;
use wcf\system\condition\type\IObjectConditionType;
use wcf\system\form\builder\field\BooleanFormField;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @implements IDatabaseObjectListConditionType<UserList<User>, bool>
 * @implements IObjectConditionType<User, bool>
 * @extends AbstractConditionType<bool>
 */
final class UserSignatureConditionType extends AbstractConditionType implements IDatabaseObjectListConditionType, IObjectConditionType
{
    #[\Override]
    public function getFormField(string $id): BooleanFormField
    {
        return BooleanFormField::create($id);
    }

    #[\Override]
    public function getIdentifier(): string
    {
        return 'signature';
    }

    #[\Override]
    public function getLabel(): string
    {
        return 'wcf.condition.user.signature';
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $objectList): void
    {
        if ($this->filter) {
            $objectList->getConditionBuilder()->add(
                "({$objectList->getDatabaseTableAlias()}.signature = ? OR {$objectList->getDatabaseTableAlias()}.signature IS NULL)",
                ['']
            );
        } else {
            $objectList->getConditionBuilder()->add(
                "({$objectList->getDatabaseTableAlias()}.signature <> ? AND {$objectList->getDatabaseTableAlias()}.signature IS NOT NULL)",
                ['']
            );
        }
    }

    #[\Override]
    public function matches(object $object): bool
    {
        if ($this->filter) {
            return $object->signature === '' || $object->signature === null;
        } else {
            return $object->signature !== '' && $object->signature !== null;
        }
    }
}
