<?php

namespace wcf\system\object\filter\user;

use wcf\data\user\User;
use Override;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\language\LanguageFactory;

/**
 * @implements IUserObjectFilter<int>
 */
final class UserLanguageObjectFilter implements IUserObjectFilter
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
    public function testValue(mixed $configuredValue, mixed $value): bool
    {
        return $configuredValue === $value;
    }

    #[Override]
    public function testUser(User $user, mixed $configuredValue): bool
    {
        return $user->languageID === $configuredValue;
    }
}
