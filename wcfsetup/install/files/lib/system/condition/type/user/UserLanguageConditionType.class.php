<?php

namespace wcf\system\condition\type\user;

use wcf\data\DatabaseObjectList;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\condition\type\AbstractConditionType;
use wcf\system\condition\type\IDatabaseObjectListConditionType;
use wcf\system\condition\type\IObjectConditionType;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\language\LanguageFactory;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @implements IDatabaseObjectListConditionType<UserList<User>, int>
 * @implements IObjectConditionType<User, int>
 * @extends AbstractConditionType<int>
 */
final class UserLanguageConditionType extends AbstractConditionType implements IDatabaseObjectListConditionType, IObjectConditionType
{
    #[\Override]
    public function getFormField(string $id): SelectFormField
    {
        return SelectFormField::create($id)
            ->options(LanguageFactory::getInstance()->getLanguages())
            ->required();
    }

    #[\Override]
    public function getIdentifier(): string
    {
        return 'language';
    }

    #[\Override]
    public function getLabel(): string
    {
        return 'wcf.condition.user.language';
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $objectList): void
    {
        $objectList->getConditionBuilder()->add(
            "{$objectList->getDatabaseTableAlias()}.languageID = ?",
            [$this->filter]
        );
    }

    #[\Override]
    public function matches(object $object): bool
    {
        return $this->filter === $object->languageID;
    }
}
