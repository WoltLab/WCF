<?php

namespace wcf\system\object\filter\user;

use wcf\data\DatabaseObject;
use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\language\LanguageFactory;
use wcf\system\object\filter\IObjectListFilter;

/**
 * Filters users by their interface language. The value is the id of the language.
 *
 * @author      Alexander Ebert, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @implements IObjectListFilter<User, int>
 */
final class UserLanguageObjectFilter implements IObjectListFilter
{
    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.userLanguage';
    }

    #[\Override]
    public function getTitle(): string
    {
        return 'TODO: user language';
    }

    #[\Override]
    public function getFormField(): SelectFormField
    {
        return SelectFormField::create('userLanguage')
            ->label('TODO: user language')
            ->options(LanguageFactory::getInstance()->getLanguages())
            ->required();
    }

    #[\Override]
    public function serializeValue(mixed $value): string
    {
        return (string)$value;
    }

    #[\Override]
    public function unserializeValue(string $serializedValue): mixed
    {
        return (int)$serializedValue;
    }

    #[\Override]
    public function summarizeValue(mixed $value): string
    {
        return \sprintf(
            'TODO: has language %s',
            LanguageFactory::getInstance()->getLanguage($value)->__toString()
        );
    }

    #[\Override]
    public function applyFilter(PreparedStatementConditionBuilder $conditions, mixed $value): void
    {
        $conditions->add('languageID = ?', [$value]);
    }

    #[\Override]
    public function testObject(DatabaseObject $object, mixed $configuredValue): bool
    {
        return $object->languageID === $configuredValue;
    }
}
