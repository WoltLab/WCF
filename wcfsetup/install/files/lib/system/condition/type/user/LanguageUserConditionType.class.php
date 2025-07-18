<?php

namespace wcf\system\condition\type\user;

use wcf\data\DatabaseObjectList;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\condition\type\AbstractConditionType;
use wcf\system\condition\type\IDatabaseObjectListConditionType;
use wcf\system\condition\type\IMigrateConditionType;
use wcf\system\condition\type\IObjectConditionType;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\language\LanguageFactory;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @implements IDatabaseObjectListConditionType<UserList<User>, string>
 * @implements IObjectConditionType<User, string>
 * @extends AbstractConditionType<string>
 */
final class LanguageUserConditionType extends AbstractConditionType implements IDatabaseObjectListConditionType, IObjectConditionType, IMigrateConditionType
{
    #[\Override]
    public function getFormField(string $id): SelectFormField
    {
        // SelectFormField stores its value as a string,
        // so we need to convert it to an integer in the `applyFilter`&`matches` method.
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
            [(int)$this->filter]
        );
    }

    #[\Override]
    public function matches(object $object): bool
    {
        return (int)$this->filter === $object->languageID;
    }

    #[\Override]
    public function getCategory(): string
    {
        return "user";
    }

    #[\Override]
    public function migrateConditionData(array &$conditionData): array
    {
        if (!isset($conditionData['languageIDs'])) {
            return [];
        }

        $result = [];
        foreach ($conditionData['languageIDs'] as $languageID) {
            $result[] = [
                'identifier' => $this->getIdentifier(),
                'value' => $languageID,
            ];
        }

        unset($conditionData['languageIDs']);

        return $result;
    }

    #[\Override]
    public function canMigrateConditionData(string $objectType): bool
    {
        return $objectType === 'com.woltlab.wcf.user.languages';
    }
}
