<?php

namespace wcf\system\object\filter\user;

use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\MultipleSelectionFormField;
use wcf\system\language\LanguageFactory;
use wcf\system\object\filter\IObjectFilter;

final class UserLanguageObjectFilter implements IObjectFilter
{
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.userLanguage';
    }

    public function getTitle(): string
    {
        return 'TODO: user language';
    }

    public function getFormField(): AbstractFormField
    {
        return MultipleSelectionFormField::create('userLanguage')
            ->label('TODO: user language')
            ->options(LanguageFactory::getInstance()->getLanguages())
            ->required();
    }

    public function serializeValue(mixed $value): string
    {
        return \json_encode($value, \JSON_THROW_ON_ERROR);
    }

    public function unserializeValue(string $serializedValue): mixed
    {
        return \json_decode($serializedValue, true, flags: \JSON_THROW_ON_ERROR);
    }

    public function summarizeValue(mixed $value): string
    {
        return \sprintf(
            'TODO: has language %s',
            \implode(
                ', ',
                \array_map(
                    static fn(int $languageID) => LanguageFactory::getInstance()->getLanguage($languageID)->__toString(),
                    $value,
                )
            )
        );
    }

    public function applyFilter(PreparedStatementConditionBuilder $conditions, mixed $value): void
    {
        $conditions->add('languageID IN (?)', [$value]);
    }

    public function testValue(mixed $configuredValue, mixed $value): bool
    {
        return \in_array($value, $configuredValue);
    }
}
